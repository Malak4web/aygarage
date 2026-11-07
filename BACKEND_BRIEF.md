# Backend brief: Laravel + MySQL for جراج أبو يوسف

The Vue storefront (in `src/`) works today on mock data (`src/data/` + `src/api/index.js` + localStorage). Your job:
- build a real Laravel API with a MySQL database;
- build an admin panel for the owner;
- connect the Vue app to them.

**Do not redesign or restyle the frontend.** Pages and components keep working as they are; only the data layer changes.

## Stack and setup

- **Laravel:** the latest stable version that installs cleanly on the PHP on this machine (PHP 8.5, Composer 2.9).
- **Folder:** create the app in `backend/` inside this project. The frontend stays at the root.
- **Database:** MySQL from XAMPP (MariaDB 10.4, started from `C:\xampp\xampp-control.exe`). Database name `aygarage`, utf8mb4. Put the connection settings in `backend/.env` and keep `.env.example` up to date.
- **Auth:** Laravel Sanctum with **API tokens** (Bearer). The frontend stores the token in localStorage and sends it in `Authorization`. Login is by **phone + password**. The "ادخل بكود على الواتساب" button stays, marked "قريباً" (WhatsApp OTP is a later phase).
- **Admin panel:** Filament, at `/admin`, in Arabic with RTL. Filament needs the PHP `intl` extension, which is **off** on this machine: enable `extension=intl` in `C:\Program Files\php\php.ini`. Tell the owner if you need admin rights to do it.
- **Dev wiring:** in `vite.config.js`, proxy `/api` and `/storage` to `http://127.0.0.1:8000`, so there is no CORS in development. Read the API base from `VITE_API_URL` (empty in dev = same origin) for production.
- **Images:** store uploads on the `public` disk (`php artisan storage:link`). Products and bikes return image URLs. When there is no image, the frontend keeps its current illustration as a fallback.
- **Language:** all validation and error messages in Arabic, returned as JSON. The frontend shows them inline.

## Database (migrations)

Use these tables. Money columns are integers in EGP; prices have no fractions today.

| Table | Main columns |
|---|---|
| `users` | name, phone (unique, stored digits-only), email nullable, password, role (`customer` / `admin`) |
| `addresses` | user_id, governorate, area, street, is_default |
| `bike_models` | code (`ZT350-T`…), name, sort. Seed the 7 Zontes models |
| `categories` | slug, name, icon, sort |
| `products` | slug, name, brand, sku, category_id, type, price, old_price nullable, stock, rating, review_count, sold_count, badge, is_best_seller, is_weekly_offer, viscosity, oil_type, fit_note, description, specs (json), fits (json array of model codes), is_active, timestamps |
| `product_options` | product_id, code (`1l`, `4l`…), label, price, old_price, sort |
| `product_images` | product_id, path, sort |
| `reviews` | product_id, user_id nullable, author_name, rating, body, is_approved |
| `used_bikes` | slug, bike_model_code, year, km, price, installment_monthly, inspection_score, owner_label, license_until, notes, specs (json), status (`available` / `reserved` / `sold`), sold_count, timestamps |
| `used_bike_inspections` | used_bike_id, section, score, note, is_warning, sort |
| `used_bike_images` | used_bike_id, path, sort |
| `user_bikes` | user_id, bike_model_code, year, plate, odometer, next_service_km, license_until, last_visit_at |
| `services` | code, name, duration_label, price nullable (null = "حسب القطع/العطل"), price_label, is_active, sort |
| `bookings` | number (unique, `BK-…`), user_id, user_bike_id nullable, custom_model nullable, odometer, service_id, date, time, price_snapshot, status (`pending` / `confirmed` / `done` / `cancelled`), notes |
| `service_records` | user_bike_id, booking_id nullable, date, odometer, work, technician, cost. This is the **maintenance history** |
| `orders` | number (unique), user_id, status (`new` / `preparing` / `shipped` / `ready_for_pickup` / `delivered` / `cancelled`), subtotal, discount, delivery_fee, total, coupon_code, delivery_method (`home` / `pickup`), payment_method (`cod` / `card` / `vodafone` / `instapay`), payment_status (`pending` / `paid`), name, phone, governorate, area, street, delivery_date, timestamps |
| `order_items` | order_id, product_id, option_code, name_snapshot, unit_price, qty, line_total |
| `coupons` | code, type (`percent` / `fixed`), value, min_subtotal, is_active, expires_at |
| `favorites` | user_id, product_id (unique pair) |
| `sell_requests` | name, phone, bike_model_code, year, km, notes, status. Comes from the "عندك موتوسيكل تبيعه؟" modal |
| `settings` | key, value. Holds everything in `src/config/store.js`: phone, WhatsApp, announcement, delivery fee, free-delivery threshold, delivery days, return days, payment methods, governorates/areas, hero kit price, etc. |

## Seeders

- Port **every** record in `src/data/*.js` and `src/config/store.js` into seeders, with the same slugs, ids/codes, prices and Arabic text, so the site looks identical after switching.
- Seed the demo customer (Karim) with phone `01012345678`, plus their bike, address, orders, bookings and service history.
- Seed one admin user. Read the admin and demo passwords from `.env` (`SEED_ADMIN_PASSWORD`, `SEED_DEMO_PASSWORD`). Don't hard-code them. In README, say where to set them.
- `php artisan migrate:fresh --seed` must give a working site.

## API (all JSON, under `/api`)

Return camelCase keys in the same shape the frontend uses today (e.g. `oldPrice`, `reviewCount`, `inspectionScore`), via API Resources, so pages don't need changes.

**Public**
- `GET /settings`
- `GET /categories`: with a real product count per category.
- `GET /products`:
  - filters: `category`, `q` (name/brand/sku), `model`, `brand[]`, `viscosity[]`, `type[]`, `minPrice`, `maxPrice`, `inStock`, `onSale`, `bestSeller`, `weeklyOffer`;
  - sort: `best` / `new` / `price_asc` / `price_desc` / `rating`;
  - paging: `page`, `perPage`.

  It returns `{ data, meta: { total, page, perPage, lastPage }, facets }`, where facets are counts per brand / viscosity / type / model for the current category.
- `GET /products/{slug}`: with options, images, and `related` (same category, 5 items). Returns 404 when missing.
- `GET /products/{slug}/reviews`: approved reviews only.
- `GET /bikes`: filters `model`, `minPrice`, `maxPrice`, `kmRange`, `minScore`, `year`, sort, with facet counts. Only `available` / `reserved` bikes.
- `GET /bikes/{slug}`: with the inspection sections, images and `similar`.
- `GET /services`
- `GET /booking/slots?date=YYYY-MM-DD`: the day's time slots with `available: true/false`. Slots and capacity per slot come from settings, default 2 bookings per slot. Past times today are unavailable.
- `POST /coupons/check` `{ code, subtotal }`: returns the discount or an Arabic error.
- `POST /sell-requests`

**Auth**
- `POST /auth/register` `{ name, phone, password, bikeModel? }`: if `bikeModel` is given, also create a `user_bikes` row.
- `POST /auth/login` `{ phone, password }`, `POST /auth/logout`, `GET /auth/me`, `PUT /auth/me` (name, email, password change).
- Rate-limit login and register.

**Customer (requires a token)**
- `GET/POST/PUT/DELETE /me/bikes`
- `GET/POST/PUT/DELETE /me/addresses`
- `GET /me/favorites`, `POST /me/favorites/{productId}`, `DELETE /me/favorites/{productId}`
- `GET /me/orders`, `GET /me/orders/{number}`
- `POST /orders`
- `GET /me/bookings`, `POST /bookings`, `POST /me/bookings/{number}/cancel` (only if the booking is in the future and not done)
- `GET /me/service-records?bikeId=`

**Rules the server must enforce. Never trust totals from the browser.**
- **Orders:** `POST /orders` receives only these from the browser: `items: [{ productId, optionCode, qty }]`, address, delivery method, payment method and coupon code. The server then:
  - reads prices from the database;
  - computes subtotal, coupon discount, delivery fee (from settings; free over the threshold; 0 for pickup) and total;
  - checks stock and decrements it inside one DB transaction with row locks;
  - returns 422 with an Arabic message per item when stock is short;
  - saves `delivery_date` (today + delivery days) on the order.

  Address fields are required only for `home`.
- **Bookings:** reject a slot that is full or in the past. `price_snapshot` comes from the service.
- **Ownership:** a user can only read their own orders, bookings, bikes, addresses and service records. Return 404 for anyone else's. The demo account is a normal account: it sees only its own data.
- Phone numbers are normalized to digits before saving or comparing.

## Admin panel (Filament, Arabic, RTL)

The owner must be able to run the shop without touching code:
- **Products:** options, images, stock, prices, old price, fits (multi-select of bike models), specs (key/value), active toggle. Categories. Reviews (approve/hide).
- **Used bikes:** inspection sections with score/note/warning, images, status.
- **Orders:** list with filters by status and date. Change status and payment status. Order detail with items.
- **Bookings:**
  - a day view or a list filtered by date;
  - change status;
  - a "تم – سجّل في سجل الصيانة" action that creates a `service_records` row (date, odometer, work, technician, cost) and updates the bike's odometer and last visit.
- **Customers:** a customer's bikes and full service history. The owner can add service records by hand.
- **Services, coupons, sell requests, settings:** settings form for everything in `settings`.
- **Dashboard:** today's bookings, new orders, low-stock products (stock ≤ 3).

Only `role = admin` can open `/admin`.

## Connecting the frontend

1. Rewrite `src/api/index.js` to call the API with fetch or axios. Keep the **same function names** and return shapes the pages use today. Add the new ones you need: `login`, `register`, `me`, `updateMe`, bikes/addresses CRUD, favorites, `getSlots`, `checkCoupon`, `createSellRequest`, `cancelBooking`.

   One shared request helper should:
   - add the token;
   - on 401, clear the user and send them to `/login?redirect=…`;
   - turn 422 errors into a field → message map.
2. **`stores/user.js`:**
   - keep the token;
   - load `me` on app start;
   - `isAuthenticated` comes from having a valid token;
   - login, register and logout call the API;
   - remove all demo-user and `defaultUser` logic.
3. **`stores/cart.js`:**
   - the cart stays in localStorage (guests can shop);
   - coupons are checked with `/coupons/check`;
   - on checkout, show server stock errors next to the right line.
4. **`stores/favorites.js`:** stays in localStorage for guests. On login, merge it into `/me/favorites`; after that, the server is the source.
5. **Listing pages:** use the server filters, sort, paging and facets instead of filtering in the browser.
6. **Booking page:** show disabled slots from `/booking/slots`.
7. **Account:**
   - every tab reads from the API;
   - add bike, edit bike and settings save to the API;
   - cancel booking.
8. **Remove from the frontend:**
   - `src/data/`;
   - every `ayg_orders` / `ayg_bookings` / `ayg_user` localStorage use;
   - the `isDemoPhone` logic.

   `src/config/store.js` keeps only fallback UI text; real values come from `GET /settings`.
9. Loading, error-with-retry and empty states already exist. Make sure they show for real network errors too: stop the backend and check.

## Payments

Phase 1:
- record the chosen method with `payment_status = pending`;
- cash on delivery and garage pickup work fully;
- for card, Vodafone Cash and InstaPay, the success page tells the customer the garage will contact them on WhatsApp to complete payment.

A real gateway (e.g. Paymob) is a later phase and needs the owner's merchant account. **Don't integrate one now.**

## Tests

Write Laravel feature tests (`php artisan test`) for at least:
- order totals: delivery fee, the free threshold, pickup = 0, coupon percent and fixed, prices taken from the DB even if the browser sends others;
- stock: an order above stock is rejected; two orders for the last unit can't both succeed;
- ownership: user A gets 404 for user B's order, booking, bike and service records;
- booking: a full slot and a past slot are rejected;
- auth: register, login with a wrong password, and rate limiting.

## Work in phases. Stop and report after each one.

1. **Setup:** Laravel in `backend/`, `.env.example`, migrations, models, seeders. `migrate:fresh --seed` works.
2. **Catalog API:** settings, categories, products, bikes, services, reviews. Connect those frontend pages. The site must look identical.
3. **Auth and account:** register, login, me, bikes, addresses, favorites. Connect Login and Account.
4. **Orders:** checkout → order → success page → Account › طلباتي, all from the API, with the stock and price rules.
5. **Bookings and maintenance history:** slots, booking, cancel, service records.
6. **Admin panel** (Filament).
7. **Cleanup:**
   - remove the mock layer;
   - write the tests;
   - update README with how to run both apps, the seed passwords setup and the deployment notes.

For each phase:
- commit;
- run `php artisan test` (from phase 7, or earlier if the tests exist);
- open every frontend route in the browser and check the console is empty.

## Definition of done

- `cd backend && php artisan migrate:fresh --seed && php artisan serve`, plus `npm run dev` at the root, gives the same site as today, with every piece of data coming from MySQL.
- Two new accounts can't see each other's orders, bookings, bikes or history. The demo account sees only its own data.
- Logging out and back in keeps everything.
- The owner can add a product with an image in `/admin` and see it on the site. When they mark a booking done, it appears in the customer's maintenance history.
- `php artisan test` passes.
- The browser console is empty on every route.
