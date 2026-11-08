# Backend review 7: review 6 fixes and phase 5 (commits 2a0434f, 8d5da2b)

Reviewed 2026-11-08, after `migrate:fresh --seed`. `php artisan test`: 28 passed (142 assertions). Phase 6 was not started, as asked.

## Review 6 fixes

Almost all done. Tested in the browser and on the API:
- **Stock error on checkout:** with 3 in the cart and stock dropped to 1, checkout shows the Arabic message on the line, with the button "عدّل الكمية للمتاح (1)". It set the cart to 1.
- **Order validation (all 422 in Arabic, none 500):**
  - `optionCode: "bogus"` gives "الاختيار ده مش متاح";
  - a product with options but no option is rejected;
  - array `productId` and `optionCode` are rejected;
  - `qty: 100` gives "أقصى كمية مسموح بها هي 99";
  - an unknown governorate is rejected.
- **Payment:** `card` and the home + pickup combination are rejected.
- **Coupons:** the placeholder no longer shows a code. Below the minimum gives "الكوبون محتاج طلب بـ 200 ج.م على الأقل". `/coupons/check` returns 429 after 10 tries.

## Phase 5

Phase 5 works end to end. Tested in the browser and on the API:
- **Booking page:**
  - slots load from `/booking/slots`, today's past slots are disabled, a full slot (15:00) is disabled, and Friday has no slots;
  - a guest who presses "تأكيد الحجز" is sent to login, and after registering is back on the booking page with the chosen model, day and time kept;
  - the booking was sent once after a double click, and `ayg_bookings` is gone.
- **Booking rules:**
  - capacity 2: the third booking returns 422 "محجوز بالكامل";
  - three simultaneous bookings for one slot gave 201, 201, 422;
  - past slots and Fridays return 422;
  - the price comes from the service (1770);
  - another user's bike returns 422.
- **Account:** "حجوزاتي" shows the booking. Cancel asks for confirmation, and the status becomes "ملغي" and frees the slot.
  - Cancelling again returns 422.
  - Another user, or Karim's BK-1042, returns 404.
  - Another user's `service-records?bikeId=` returns 404.
- **Console:** empty.

Fix these, then start phase 6. Paths: `backend/` for PHP, `src/` for Vue.

## A. Must fix

1. **The English "(and 1 more error)" is still there.** I checked this on the API: an array `productId` gives "معرّف المنتج غير صالح (and 1 more error)", and `paymentMethod: card` gives "طريقة الدفع غير صالحة… (and 1 more error)".
   - **Cause:** the key in `lang/ar.json` is wrong. Laravel looks up `"(and :count more error)"` and `"(and :count more errors)"` (see `ValidationException::summarize`), not `":message (and …)"`.
   - **Fix:** use these keys:

     ```json
     "(and :count more error)": "(وخطأ تاني)",
     "(and :count more errors)": "(و :count أخطاء تانية)"
     ```

   - Also send one key per problem where possible (the array `productId` sends both `items.0.productId` and `items.0`).
2. **A booking can be made at any time,** not only at the garage's slots. I checked this on the API: `12:00` and `17:00` (marked closed), `11:37` and `03:00` were all accepted with 201.
   - In `BookingCreateRequest`, accept only a `time` that is in `booking_slots` and not `off`. Return "الميعاد ده مش متاح" otherwise.
   - Also limit the date to today through 30 days ahead (or a `booking_days_ahead` setting). `2099-01-01` was accepted.
3. **500 on `/booking/slots?date[]=x`.** I checked this on the API. Validate `date` with a FormRequest (`required|date_format:Y-m-d`). Also return 422 for a date before today or beyond the booking window.
4. **An inactive service can be booked.** I checked this on the API: with `s6` set `is_active=0`, a booking with `s6` was accepted. Check `is_active`. Also make `service_id` a string rule: an array `service_id: ["s1"]` was accepted.

5. **After midnight, the booking page sends the wrong day.** `BookingPage.vue:276` builds the date with `toISOString()`, which is in UTC. From midnight to 02:59 Cairo time, the page shows today but sends yesterday. Build `YYYY-MM-DD` from local parts (or from Africa/Cairo), and use the same date for the Friday check.
6. **Saving a booking can return 500 under load.**
   - `lockForUpdate()->count()` on an empty slot only takes gap locks. On MySQL, two requests can deadlock.
   - The `BK-` number is built from the last row under a lock, so two requests can try the same number and hit the unique index.

   In my test, three simultaneous bookings gave 201, 201, 422, so it doesn't fail every time. But neither case should ever return 500.
   - Use `DB::transaction(fn, 3)` so a deadlock retries.
   - Generate the number after the insert (`'BK-'.(1042 + id)`) or retry on a duplicate.

## B. Fix in this round

- **If `/booking/slots` fails,** the page silently shows the built-in slots as available. Show "تعذر تحميل المواعيد" with a retry instead.
  - Ignore slow responses from older date clicks.
  - After a 422 "محجوز بالكامل", reload the slots.
  - When all of today's slots are past, select the next day.
- **Cancel:** remove the empty `catch {}` in `BookingController::cancel` (:258-260). If the date can't be read, refuse the cancel.
  - `BookingResource` must not crash on a bad time saved later from the admin. Fall back to the raw value.
- **Checkout `lineErrors`** are keyed by array index, so after "شيل من السلة" the other errors move onto the wrong lines. Key them by `lineId`. An option error should not show "شيل من السلة" as if stock were 0.
- **Payment methods** are hardcoded as `in:cod,pickup`. Read them from the `payment_methods` setting, as review 6 item 6 asked, so the admin can change them in phase 6.

7. **The WhatsApp reminder is a promise nothing keeps.** The success message says "هيوصلك تذكير على الواتساب قبل الميعاد بساعتين" (`BookingPage.vue:17`), but there is no WhatsApp integration. Use "هنكلمك نأكد الميعاد" or remove it, unless the owner plans to send reminders by hand.
8. **The guest booking page** says "معندكش موتوسيكلات مسجلة في حسابك" to someone who has no account. For a guest, show "سجّل دخول عشان تختار موتوسيكلك، أو اكتب الموديل تحت". Better still, tell them before they fill everything in that confirming needs an account.
9. **The date in "حجوزاتي"** shows `2026-11-09 · 1:00 م`. Show it in Arabic like the rest of the site ("الاثنين 9 نوفمبر · 1:00 م").
10. **Order of checks in `BookingController@store`:** an unknown service returns "هذا الموعد محجوز بالكامل" when the slot is full. Validate the service first (in the FormRequest), then check capacity.
11. **The 12:00 and 17:00 slots are closed every day.** That came from the old demo design, where they showed as "full". Ask the owner whether they are a real break. If not, set `off: false` in the seeder. Either way, phase 6 must let the owner edit `booking_slots` and `booking_capacity_per_slot`.
12. **Tests:** add tests for:
    - a time outside the slots, an off slot, `25:00`, an earlier time today, and Friday;
    - a date beyond the window, and `date[]` on `/booking/slots`;
    - an inactive service;
    - another user's bike;
    - the cancel rules: past, done, already cancelled;
    - a cancelled booking freeing its slot.
13. **Seed:** Karim's BK-1042 is set to now + 5 days, so on some days it lands on a Friday. Pick the next non-Friday date. Also add one past booking with status `done`, linked to a service record.

## Done when

- **Error messages:** no English "(and … more error)" anywhere: check the API responses for array `productId` and `paymentMethod: card`.
- **Booking rules:**
  - `POST /bookings` with `time: "11:37"`, `"12:00"` or `date: "2099-01-01"` returns 422;
  - `/booking/slots?date[]=x` returns 422;
  - an inactive service returns 422.
- **Tests:** `php artisan test` passes with the new tests.
- Commit, then start **phase 6 (Filament admin)** from BACKEND_BRIEF.md in the same round. `intl` must be enabled first. If `php -m` doesn't list it, stop and tell the owner.

  Commit, report, and stop for review.
