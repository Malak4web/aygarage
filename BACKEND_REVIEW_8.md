# Backend review 8: review 7 fixes and phase 6 (commits 0c440f9, c03699f)

Reviewed 2026-11-08, after `migrate:fresh --seed`. `php artisan test`: 39 passed (204 assertions). `intl` is enabled.

## Review 7 fixes

All done. Tested on the API:
- **Off-slot times:** `11:37`, `12:00`, `25:00` and `03:00` return "الميعاد ده مش متاح".
- **Booking window:** `2099-01-01` and today + 40 days return 422 (30-day window). `/booking/slots` returns 422 for `date[]=x`, a past date or a far date.
- **Services:** an inactive service, or an array `service_id`, returns 422.
- **Error messages:** no English "(and … more error)" anymore. "(وخطأ تاني)" shows instead.
- **Simultaneous bookings:** three at once for one slot gave 201, 201, 422.
- **Frontend:** booking date from local parts, slots error with retry, Arabic date in "حجوزاتي", and the seed fixes.

One gap remains in item 6. The `BK-` number still comes from the last row plus a `while exists()` loop. Under MySQL REPEATABLE READ, that read uses the transaction's snapshot, so two requests can still pick the same number. A duplicate key (1062) is not retried and returns 500. Generate the number after the insert: `'BK-'.(1042 + $booking->id)`.

## Phase 6: the admin panel

The panel is good: Arabic, RTL, clear navigation groups. Tested in the browser with a temporary admin, deleted afterwards:
- **Dashboard:** today's bookings, new orders, low stock (≤ 3), bikes for sale.
- **Booking action:** "تم – سجّل في سجل الصيانة" on BK-1042 opens a form prefilled from the booking. After submit, a `service_records` row was created, the bike's odometer became 12900, and the booking became "مكتمل".
- **Order status:** cancelling an order in admin shows "ملغي" in the customer's "طلباتي".
- **Product price:** changing a price in admin shows on the site at once.
- **Access:** only `role = admin` can enter.

But the owner can't run the shop with it yet. Paths: `backend/` for PHP.

## A. Must fix

1. **The owner can't change the status of a pickup order.** I checked this in the browser: on order 1043 (pickup), payment method shows "اختر", because `pickup` is missing from the options (`OrderForm.php`). Saving stops with the browser message "Please select an item in the list". Every pickup order is stuck at "طلب جديد".
2. **Orders are edited like a spreadsheet.**
   - Subtotal, discount, delivery fee, total and the items (product, price, qty) are all editable fields, and nothing recomputes them.
   - There is a "إضافة طلب" page, but `number` is not saved, so creating an order fails with SQL 500. Even if it saved, it would skip stock and totals.
   - Orders can be deleted.

   **Fix:**
   - Remove create and delete.
   - Show totals and items read-only (infolist or disabled fields).
   - Allow editing only status, payment status, delivery date, and the customer's name, phone and address.
3. **Cancelling an order doesn't return the stock.** I checked this in the browser: order 1044 (2 Brembo pads) cancelled in admin, and stock stayed at 11.
   - When status changes to `cancelled`, increment stock for each item, in a transaction, once only.
   - Changing back from `cancelled` must re-check and decrement it again, or be blocked.
4. **No image upload.** Product and used-bike images are a text field for a path. The URL becomes `/storage//images/...`, which is broken.
   - Use `FileUpload` on the `public` disk (images only, max 2 MB, multiple, reorderable), and save the relative path that `ProductResource` / `UsedBikeResource` turn into a URL.
   - Check that a new image shows on the product page and on the card.
5. **Settings is a raw key/value editor.** The owner sees keys like `booking_slots` with raw JSON.
   - The value type is guessed. A phone without spaces like `01000000000` is saved as the number 1000000000 and loses its leading 0.
   - Broken JSON in `booking_slots` or `governorates` is saved as text. `/booking/slots` and `POST /bookings` then crash with 500 (`foreach` over a string, `$s['time']`).
   - Keys can be deleted or invented.

   **Fix:** a single "الإعدادات" page (a custom Filament page) with typed fields per group:
   - **Store:** name, phone, WhatsApp (text), announcement.
   - **Delivery:** fee, free threshold, delivery days (numeric), estimate text.
   - **Booking:** slots as a repeater (time, label, closed toggle), capacity per slot, days ahead.
   - **Areas:** governorates as a repeater (name plus areas tags).
   - **Payment methods:** a repeater (id, title, desc), limited to `cod` / `pickup` for now.
   - **Other:** return days, trust texts.

   Save each with the right type. Remove create and delete for settings. Read slots and governorates defensively, so a bad value never returns 500.
6. **The booking action:**
   - For a booking with `custom_model` (no registered bike), it attaches the record to the customer's **first** bike, which may be the wrong one.
   - If the customer has no bike, it marks the booking done, saves no record, and still says it succeeded.
   - **Fix:**
     - let the admin choose the bike from that customer's bikes, or create a new bike from `custom_model`;
     - run the action in a transaction;
     - also set `next_service_km = odometer + 3000`: it stayed 15000 after the odometer became 12900;
     - don't allow a date after today. BK-1042 was recorded on 14 November, a future date.
   - The test `AdminPanelTest.php:77-130` re-implements this logic instead of calling the action. Use a Livewire test that calls the table action.

## B. Fix in this round

7. **Bookings in admin:**
   - "إضافة حجز صيانة" fails with 500, because `number` is not saved. Generate it in the model's `creating` event, so admin, API and seeders all use one rule.
   - `user_bike_id` lists every customer's bikes. Filter it by the selected customer.
   - `time` is free text. Use a select from `booking_slots`.
8. **Customers (Users):**
   - Validate the phone after normalizing (`Phone::normalize` plus the `01…` regex), so `+20 10…` doesn't pass validation and then hit the DB unique index (500).
   - Block the admin from demoting or deleting themselves or the last admin.
9. **Validation:**
   - Coupons: value ≥ 1, percent ≤ 100. A negative fixed coupon would raise the total.
   - Products: price, old price and stock ≥ 0; old price > price when filled.
   - Inspection scores: 0–100.
   - Option codes unique per product.
10. **A product with options:** the main price and the option prices are separate fields. I changed Motul's price to 1500, and the 1 L option stayed 1450. Either compute the main price from the first or cheapest option and hide the field, or explain under it "السعر الأساسي بيظهر في الكارت، وأسعار الأحجام من الخيارات".
11. **Admin login:** the login asks for an email (`admin@aygarage.com`), but the site uses phone numbers, and the README doesn't say which email. Use a phone login for the panel (a custom Login page that uses `phone`), or at least write the admin email in the README next to `SEED_ADMIN_PASSWORD`.
12. **Smaller:**
    - the dashboard counts cancelled bookings as today's bookings;
    - low stock includes inactive products;
    - prices show as "٢٬٨٥٠٫٠٠ ج.م." with decimals: use `->money('EGP', 0)` or plain numbers with "ج.م";
    - English left: the "Slug" label, and `filament-forms::components.repeater.reordering.position` shows as raw text in the order page;
    - generate the slug from the name automatically, still editable;
    - the order page's "طريقة الدفع" lists Vodafone, InstaPay and card: show only the methods from settings;
    - `last_visit_at` was added by editing the old `create_user_bikes_table` migration. Add a new migration instead, so a database that isn't rebuilt still gets the column;
    - `role` is back in `User::$fillable`. Remove it, and set the role explicitly in the admin form.
13. **Tests:** Livewire tests for:
    - changing a pickup order's status;
    - cancelling an order (stock returns);
    - the booking action (record plus odometer);
    - saving settings with a wrong value (no 500);
    - creating a booking from admin.

## Done when

- **Pickup orders:** changing status saves.
- **Order cancel:** cancelling returns the stock, and totals and items can't be edited.
- **Images:** uploading an image to a product shows it on the product page.
- **Settings:** the owner can change the delivery fee, the booking slots and the governorates from one settings page without writing JSON, and the site uses the new values.
- **Booking action:** on a booking without a registered bike, the admin picks or creates the bike. The record shows in the customer's "سجل الصيانة".
- **Tests:** `php artisan test` passes with the new tests.
- Commit, report, and stop. This is the last phase in the brief. After this review comes a full end-to-end pass, including deployment notes.
