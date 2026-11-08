# Backend review 1: phase 1 (commit b06db8c)

Reviewed 2026-11-07. Phase 1 is in good shape:
- `migrate:fresh --seed` runs cleanly on the XAMPP MySQL.
- Arabic is stored correctly (utf8mb4_unicode_ci).
- All 16 products, 3 options, 6 bikes, 36 inspections, 8 categories, 6 services and 7 models match `src/data` field for field.
- `.env` and `vendor/` are not committed.
- Sanctum is installed.

Round 5 frontend sections B and C were also checked in the browser and all pass.

Fix the items below **before starting phase 2**. Paths are under `backend/`.

## A. Data safety

1. **Deletes that wipe history.** Change these foreign keys:
   - `order_items.product_id` (create_order_items_table:14): nullable + `nullOnDelete()`. `name_snapshot` keeps the name.
   - `products.category_id` (create_products_table:17): `restrictOnDelete()`.
   - `bookings.service_id` (create_bookings_table:18): `restrictOnDelete()`.
   - `orders.user_id` (create_orders_table:14): `nullOnDelete()`.

   In the admin panel later, products and bikes should be deactivated (`is_active` / `status`), not deleted.
2. `app/Models/User.php:21`: remove `role` from `$fillable`. Set it explicitly in the seeder and in admin only. Registration must never be able to set it.
3. `UserSeeder.php:23, :35`: remove the fallback passwords from `env(...)`. If the variable is missing, stop with a clear error. In `.env.example`, leave `SEED_ADMIN_PASSWORD=` and `SEED_DEMO_PASSWORD=` empty, with a comment.

## B. Seed data gaps (the site must look the same after switching)

4. Seed `created_at` for products and bikes from the mock `createdAt`, so the "الأحدث" sort keeps its order.
5. Bike `zt350t-2023` has 3 service-history entries in the mock, and the bike page shows them. Add a `used_bike_service_records` table (used_bike_id, date, odometer, work, technician), seed it, and return it with the bike.
6. `user_bikes.last_visit_at` is a string ("12 أكتوبر"). Make it a `date`, or drop it and compute it from the latest `service_records` row. That is better, because it can never go stale.
7. **Booking times:** match the frontend exactly.
   - Slots are 11:00 ص to 8:00 م, every hour (see `src/pages/BookingPage.vue:293`), including which ones are off.
   - Store times as `HH:MM` 24h in the DB (`time` column) and format them in Arabic in the API.
   - The demo booking must use one of those slots.
8. **Reviews:** the mock showed the same 3 reviews on every product. Seed 2 to 3 reviews on at least 5 products, the best sellers. Keep products with no reviews, so the empty state is tested.
9. **Category counts:** the API will return the **real** product count. That is correct; don't copy the fake 48/24 numbers. Note it in README.
10. **Settings:** also seed `trust_items` (text + icon). Compute `heroKitPriceFormatted` and `deliveryEstimate` in the API, not in the DB.

## C. Setup still missing

11. **`intl` is still not enabled** (`php -m` doesn't list it), so Filament can't be installed. The owner will enable it by hand (see below). Don't change `php.ini` yourself. Before phase 6, confirm `php -m` shows `intl`.
12. Set `FILESYSTEM_DISK=public` and run `php artisan storage:link`. Add both to README.
13. Add Arabic validation messages: `lang/ar/validation.php` with attribute names (رقم الموبايل, كلمة المرور…), and set `APP_LOCALE=ar`.
14. Replace the stock Laravel README in `backend/` with:
    - how to start MySQL from XAMPP;
    - create the database `aygarage`;
    - copy `.env.example` → `.env`, `key:generate`, set the seed passwords;
    - `migrate:fresh --seed`, `serve`;
    - the demo and admin phone numbers.

## D. Notes for phase 2 (no action now)

- Settings keys are snake_case in the DB, and the frontend reads camelCase. Map them in the `/settings` resource.
- The frontend uses string ids (`p1`, `b1`, `s1`) inside localStorage carts and favorites. The API identifies products by `slug` and options by `code`. Phase 2 must map them, and old carts holding `p1` must be migrated or cleared on first load.
- Return order and booking status with an Arabic `statusLabel` from the API: `delivered` + `pickup` → "تم الاستلام", `delivered` + `home` → "تم التوصيل", etc.
- `stores/cart.js` hard-codes the ZONTES10 coupon. Phase 4 switches it to `/coupons/check`.

## Done when

- `migrate:fresh --seed` still works, fails clearly when the seed passwords are missing, and the items above are in.
- Commit, report, then go to phase 2.
