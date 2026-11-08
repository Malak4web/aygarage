# Backend review 5: review 4 fixes (commits b8b911e, 7992cd8)

Reviewed 2026-11-08, after `migrate:fresh --seed`. Phase 4 was not started, as asked.

Almost all of review 4 is done. Tested in the browser and on the API:
- **Expired login:** with a fake token, one `/auth/me` 401, then the user is logged out and sent to `/login?redirect=/account`. No repeated requests.
- **New account:** orders, bookings and bikes are empty, with no Karim data anywhere.
- **Phone:**
  - Registering in the browser with `+20 ١٠…` (Arabic digits) saved `010…`.
  - `+20 15…`, then `015…` gives "الرقم ده مسجل قبل كده، سجّل دخول بدل كده".
  - Logging in with Arabic digits works.
  - `01234`, `abc123`, `0201…` are rejected with the new message.
- **Favorites:** a guest heart is saved by slug. After registering it moved to the account, and `ayg_favorites` was removed. A heart click while logged in saves to `/me/favorites`. An inactive or unknown product returns 404.
- **Filters:** `?maxPrice=500` and `?maxScore=89` return 200; `minPrice > maxPrice` returns 422.
- **Errors:** a request without `Accept` returns a JSON 401 in Arabic. 429 is in Arabic.
- **Rate limits:** sell requests are limited to 5 per minute, and login and register have separate limiters.
- **Settings:** saving twice updates the same address (no duplicates). Phone is read-only. A new user's fields start empty.
- **Password change:** works, keeps the current session, and logs out the other sessions.
- **Other:** WhatsApp shows "(قريباً)", only cash on delivery and pay at the garage are offered, `role` can't be raised, and the console is empty.

Fix these small items, then start phase 4. Paths: `backend/` for PHP, `src/` for Vue.

## A. Must fix

1. **The old favorites map is wrong** (`src/stores/favorites.js:4-21`). 12 of 16 entries are wrong. Two of them point to a real but different product, so a returning customer gets the wrong item in favorites:
   - `p12` maps to Shell, but should be Liqui Moly;
   - `p13` maps to Mobil 1, but should be Shell.

   The correct map, from the old `products.js` and `ProductSeeder`:

   | id | slug |
   |---|---|
   | p1 | motul-7100-10w40 |
   | p2 | zontes-oil-filter-zt350 |
   | p3 | brembo-front-brake-pads |
   | p4 | ls2-rapid-ii-helmet |
   | p5 | did-520-vx3-chain |
   | p6 | ngk-iridium-cr8eix |
   | p7 | alpinestars-smx-1-air |
   | p8 | yuasa-ytz10s-battery |
   | p9 | motul-5100-15w50 |
   | p10 | pirelli-angel-st-rear |
   | p11 | castrol-power1-racing-10w40 |
   | p12 | liqui-moly-street-race-10w40 |
   | p13 | shell-advance-ultra-10w40 |
   | p14 | mobil-1-racing-4t-15w50 |
   | p15 | motul-300v-factory-line-10w40 |
   | p16 | repsol-moto-racing-10w40 |

   **The merge also retries forever.** Any 404 keeps `mergeSuccess=false`, so `ayg_favorites` is never removed, and on every reload it re-adds favorites the user removed while logged in. Treat a 404 as done (drop that entry), and keep the localStorage copy only for network or 5xx errors.
2. **Field errors show one letter.** `request()` already turns errors into `{ field: "message" }`, but AccountPage prints `errors.x[0]`. I checked this in the browser: a wrong current password shows only "ك". The same happens in Settings (`name`, `governorate`, `area`, `street`) and in the password form. Print the string itself (`AccountPage.vue:352-384, 410, 421, 878`).
3. **`/me/bikes` accepts any model.** I checked this on the API: `bikeModelCode: "FAKE"` and `model: "<b>hack</b>"` were both saved, with 201. Validate `bikeModelCode` with `exists:bike_models,code`, as in Register. Build `model_name` from the `bike_models` table, not from the request. Validate `licenseUntil` too, if it's sent.

## B. Smaller

4. **`/auth/me` has the N+1 query** that `/me/bikes` no longer has. The frontend reads bikes from `/auth/me`, so load `bikes.latestServiceRecord` in `AuthController` (lines 44, 67, 83, 119). Remove the dead `UserBike::getLastVisitAtAttribute`.
5. **The empty service history** shows only the table headers. Add an empty state: "لسه مفيش زيارات صيانة مسجلة".
6. **ProductPage** shows "يركب على موتوسيكلك ZT350-T · " with nothing after the dot when the year is empty (`ProductPage.vue:125`). Show the dot only when there is a year.
7. **After a 401**, `clearSession` clears the guest favorite ids in memory, so the header count reads 0 until reload. Reload them from localStorage.
8. **Settings can't clear an address:** when the street is emptied, the update is skipped. Either delete the address or show an error under the field.
9. **Cart fallback line** (when the refresh fails):
   - it shows the raw slug;
   - `removeBySlug(item.lineId)` passes a lineId;
   - it removes every option with that slug.

   Use `removeItem(item.lineId)`. Show the name if it was saved, or "منتج".
10. **Cleanup:**
    - `AccountPage.vue:634` still passes `p.id` to favorites;
    - remove `'role' => 'customer'` in `AuthController.php:25` (the DB default covers it);
    - `/categories` is requested 3 times on start. Load it once in a store.

## Done when

- **Old favorites:** as a guest, put `["p2","p12","p99"]` in `ayg_favorites` and register. The account shows the Zontes filter and Liqui Moly, and `ayg_favorites` is removed.
- **Field errors:** a wrong current password shows the full message under the field.
- **Bike model:** `POST /me/bikes` with `bikeModelCode: "FAKE"` returns 422 in Arabic.
- **Console:** empty.
- Commit, then start **phase 4 (orders and coupons)** from BACKEND_BRIEF.md in the same round:
  - orders and totals computed on the server (reuse `slugs[]`);
  - stock decremented in a transaction;
  - `POST /coupons/check`;
  - checkout and the account's "طلباتي" connected to the API;
  - the phone in checkout normalized with `Phone::normalize()`.

  Commit, report, and stop for review.
