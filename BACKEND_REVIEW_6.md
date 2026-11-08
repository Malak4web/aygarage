# Backend review 6: review 5 fixes and phase 4 (commits c873ec1, ce1f362)

Reviewed 2026-11-08, after `migrate:fresh --seed`. `php artisan test`: 13 passed. Phase 5 was not started, as asked.

## Review 5 fixes

All 10 items are done. Tested in the browser:
- **Old favorites:** as a guest, `["p2","p12","p99"]` became the Zontes filter and Liqui Moly after registering. `p99` was dropped, and `ayg_favorites` was removed.
- **Bike model:** `bikeModelCode: "FAKE"` returns 422, and `model_name` comes from `bike_models`.
- **Other fixes:** field errors print the full message; `/auth/me` loads `latestServiceRecord`; the empty history state; emptying the street deletes the address; the cart fallback line; the single `/categories` request.

## Phase 4

Phase 4 works end to end. Tested in the browser and on the API:
- **Checkout:**
  - a guest is sent to `/login?redirect=/checkout`, and after registering lands back on checkout;
  - the order submits once even after a double click;
  - the cart is cleared, and `/order/1053` loads from the API, including after a reload;
  - "طلباتي" shows the order from `/me/orders`.
- **Totals on the server:**
  - an order sent with `price: 1`, `total: 1` was saved at the DB price (680 + 60 delivery);
  - delivery is free from 3000, and pickup is 0;
  - the 4 L option is priced 5400;
  - ZONTES10 gives 10%, in lowercase too, and GARAGE50 respects its 200 minimum.
- **Stock:**
  - above stock and out of stock return 422 in Arabic;
  - stock is decremented;
  - two simultaneous orders for the last unit gave 201 and 422.
- **Validation:** qty 0, -1, 1.5 and `abc` return 422; home delivery without an address returns 422; the phone is normalized.
- **Ownership:** user B gets 404 for user A's order, and a new user gets 404 for Karim's #1042.
- **Delivery date:** today + 3 days (11 November), the same on the checkout page and the server.
- **Console:** empty.

Fix these, then start phase 5. Paths: `backend/` for PHP, `src/` for Vue.

## A. Must fix

1. **Stock errors on checkout.** I checked this in the browser: with 3 in the cart and stock dropped to 1, checkout shows "الكمية المطلوبة من تيل فرامل … (المتاح: 1) **(and 1 more error)**" in red under the total.
   - **English suffix:** `OrderController.php:119-128` always sends two keys (`items.N.qty` and `items`), so Laravel appends "(and 1 more error)". Send one key per line and set the `message` yourself. Also add `lang/ar.json` for Laravel's default strings.
   - **Show the error on the right line:** the brief asks for the error next to the line, with a button "عدّل الكمية للمتاح (1)" that sets the cart qty. Today the customer has to go back to the cart and guess. When stock is 0, offer "شيل من السلة".
2. **An invalid option is accepted** (`OrderController.php:154-162`). I checked this on the API: `optionCode: "bogus"` was saved as order 1049 at the base price, with `option_code = bogus`, which leaves the garage guessing what to send.
   - Return 422 "الاختيار ده مش متاح" for an unknown option.
   - For a product that has options, require one.
   - Load all options in one query, not one per line.
3. **500 with a stack trace from array inputs.** I checked this on the API: `items[0].productId: ["x"]` and `items[0].optionCode: ["a"]` both return 500 "Array to string conversion". Add rules:
   - `items.*.productId` (or `slug`): required, string, max:100;
   - `items.*.optionCode`: nullable, string, max:20;
   - `items`: max:50;
   - `items.*.qty`: max:99.
4. **The coupon code is shown to everyone.** The cart placeholder reads "كود الخصم (جرب ZONTES10)" (`CartPage.vue:184`). Use "كود الخصم".
   - Also remove the `ZONTES10` fallback in `cart.js:87-89`.
   - When the subtotal falls below the coupon's minimum, remove the coupon and say "الكوبون محتاج طلب بـ 200 ج.م على الأقل". Return that message from the server too, instead of "غير صحيح أو منتهي الصلاحية".
5. **`/coupons/check` has no rate limit.** I sent 15 wrong codes in a row, and none were blocked. Add `throttle:10,1`.

## B. Fix in this round

6. **Payment methods:** `card`, `vodafone` and `instapay` are still accepted (order 1050 was saved as `card`). `delivery_method=home` with `payment_method=pickup` is also accepted (order 1054).
   - Allow only `cod` and `pickup`, taken from the `payment_methods` setting.
   - Require `pickup` payment with `pickup` delivery, and `cod` with `home`.
7. **Settings:** use `Setting::get()` (with the json cast), not raw `value('value')`, for `delivery_fee`, `free_delivery_threshold` and `delivery_days_count` (`OrderController.php:194-195, 207`). Otherwise an admin who saves `"60"` in phase 6 gives free delivery.
8. **Coupons:** in `Coupon.php`, cap a percent discount at 100 and any discount at the subtotal. In phase 6, validate `value` in the admin form.
9. **"طلباتي" order:** sort by `created_at desc`, not `id` (`OrderController.php:25`). Today Karim's September order shows above the November one.
10. **Smaller:**
    - the success page address leaves out the governorate ("شارع مكرم عبيد 5، مدينة نصر");
    - validate governorate and area against the settings lists;
    - remove the unused eager load `items.product.category`;
    - the sequential number lock is fine for now; just note it in the README.
11. **Tests:**
    - make the "last unit" test truly concurrent, or at least assert `lockForUpdate` is used;
    - add tests for an invalid option, an inactive product, array inputs (expect 422), a percent coupon on `/orders`, and a payment method other than cod/pickup.

## Done when

- **Stock:** with 3 in the cart and stock 1, checkout shows the Arabic message next to the line, with no English, and the button sets qty to 1.
- **Bad input:** `optionCode: "bogus"` and array inputs return 422, never 500.
- **Coupons:** the placeholder doesn't show any code, and `/coupons/check` returns 429 after 10 tries.
- **Payment:** `paymentMethod: card` returns 422.
- **Tests:** `php artisan test` passes with the new tests.
- Commit, then start **phase 5 (bookings)** from BACKEND_BRIEF.md in the same round:
  - `GET /booking/slots`;
  - `POST /bookings`, which rejects full and past slots (Africa/Cairo time), with `price_snapshot` from the service;
  - `GET /me/bookings` and `POST /me/bookings/{number}/cancel`;
  - `GET /me/service-records?bikeId=`;
  - the booking page shows disabled slots;
  - the account's "حجوزاتي" and "سجل الصيانة" read from the API;
  - remove `ayg_bookings`;
  - normalize the booking phone with `Phone::normalize()`;
  - tests: a full slot, a past slot, and ownership.

  Commit, report, and stop for review.
