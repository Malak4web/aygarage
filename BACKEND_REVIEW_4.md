# Backend review 4: review 3 fixes and phase 3 (commits 769d1c6..17d4f53)

Reviewed 2026-11-08.

## Review 3 fixes

All review 3 fixes are in. Tested in the browser:
- **Cart after reload:** the cart and checkout render after a reload, and the header total is right on every page.
- **Price and stock changes:** I changed a price and set a product out of stock in the DB, then reloaded. The cart box showed both changes:
  - "تغير سعر … من 680 إلى 750 ج.م";
  - "… نفد من المخزن واتشال من السلة".
- **Universal products:** with a bike selected, `/c/helmets` shows the helmets.
- **Other fixes:** search paging, a 404 for an unknown category, `slugs[]`, the reviews empty state, and "فحصنا أكتر من".

## Phase 3

Phase 3 works for the basic flow:
- **Register:** creating an account works, and a taken phone shows the error under the field.
- **Session:** `?redirect` is honored. Reloading keeps the user logged in.
- **Account data:** adding a bike and saving an address go to MySQL.
- **Logout:** it revokes the token.
- **Ownership:** another user's address or bike returns 404 on PUT and DELETE.
- **Role:** `role` cannot be set through register or `PUT /auth/me`.
- **Sell form:** it posts to the server, and the extra fields (`status`, `user_id`) are ignored.

But there are serious bugs. Fix them before phase 4. Paths: `backend/` for PHP, `src/` for Vue.

## A. Must fix

1. **Infinite request loop on an expired login.** I checked this in the browser: with an old token (after `migrate:fresh`, or a token deleted in admin), the site sends `POST /auth/logout` without end. I counted 60+ requests in a few seconds.
   - **Cause:** on a 401, `request()` (`api/index.js:34-43`) calls `userStore.logout()`. That calls `api.logout()`, which gets a 401, which calls `logout()` again.
   - **Fix:**
     - add `clearSession()`, which clears the token, user and favorites locally without calling the API, and use it in the 401 branch;
     - `logout()` calls the API only when a token exists;
     - never run the 401 handling for `/auth/logout` itself.
2. **A new account shows Karim's orders and service history.** I checked this in the browser: a new user sees orders #1042 and #0987 and 4 service visits, including under their own new bike. Round 4 had fixed this, and it came back.
   - **Cause:**
     - `getUserOrders` and `getUserBookings` read `ayg_user`, which is no longer written, so they fall back to `defaultUser`;
     - `getServiceHistory()` returns the demo history to everyone;
     - `getUser()` falls back to `defaultUser` on any error, so a network failure shows Karim's identity.
   - **Fix:**
     - Until phases 4-5, return `[]` for orders, bookings and history.
     - Remove every `defaultUser` / `serviceHistory` import from `api/index.js`.
     - Delete `src/data/user.js` and `src/data/serviceHistory.js`.
     - The demo data lives in the DB seeders now. Phases 4-5 will serve it from the API.
3. **Phone numbers:**
   - `+20 10 1234 5678` is stored as `201012345678`. That creates a second account for the same person, and the demo user can't log in with +20. The DB already has duplicates.
   - Arabic digits (٠١٠…) are stripped, which gives "رقم الموبايل مطلوب".
   - `01234` and `abc123` are accepted.

   **Fix:** one `App\Support\Phone::normalize()`, used in register, login, sell requests and the `User` mutator:
   - convert ٠-٩ and ۰-۹ to 0-9;
   - strip everything else;
   - turn a leading `0020` or `20` into `0`;
   - then validate `regex:/^01[0125]\d{8}$/` with the message "رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 01".

   Do the same in the frontend before sending.
4. **Favorites never save for a logged-in user, and guest favorites are lost.** I checked this in the browser: clicking the heart while logged in gives `Failed to update favorite on server: المنتج غير موجود`. After registering, the guest favorite was gone.
   - **Cause:** `ProductCard.vue:90,93` sends `product.id` (`p16`), but the server looks up by slug.
   - **Fix:**
     - Use `product.slug` everywhere: the store, the card, the product page and the account page.
     - Convert the old `p…` ids in localStorage once.
     - Remove `ayg_favorites` only after the merge succeeds.
     - Don't accept or list inactive products.
5. **A max-only price filter breaks the page.** On the API, `?maxPrice=500` and `?maxScore=89` return 422. The frontend leaves out `minPrice` when it is 0, so setting only a max price puts Category and Bikes into "إعادة المحاولة".
   - Apply `gte:minPrice` only when `minPrice` is filled (`exclude_without` or a closure). Same for `maxScore`.

## B. Fix in this round

6. **500 with a stack trace:** an unauthenticated `/api/me/*` request without an `Accept` header returns `Route [login] not defined`.
   - In `bootstrap/app.php`, render JSON for `api/*` (`shouldRenderJsonWhen`), and return `null` from `redirectGuestsTo` for the API.
   - Put Arabic messages on 401 ("لازم تسجّل دخول الأول") and 429 ("محاولات كتير، استنى دقيقة وحاول تاني").
   - Note in the README that production needs `APP_DEBUG=false`.
7. **Every Settings save adds a new address.** `AccountPage` calls POST each time. Use `updateAddress(id)` when an address exists.
   - The phone field is editable but never saved. Make it read-only.
   - Show the 422 errors under the fields, not only as a toast.
   - A new user's Settings is prefilled with "القاهرة / مدينة نصر". Leave the fields empty.
8. **Fake bike data:**
   - A new bike shows "الرخصة 2027" with no license date entered.
   - The demo bike's `lastVisit` is `1970-01-01`: `UserBikeResource.php:29` runs `strtotime` on Arabic text.
   - **Fix:**
     - return null when there is no value, and show "—";
     - compute `lastVisit` from the latest `service_records.date`;
     - eager-load `latestServiceRecord` (this also fixes the N+1 query);
     - remove the unused `with('serviceRecords')`.
9. **`/sell-requests` has no rate limit:** I sent 9 in a row and all were accepted. Add `throttle:5,1` and the phone rule from item 3.
   - Use a separate limiter for login and register too. They share one `throttle:10,1` key per IP.
10. **The phase 3 brief is not complete:**
    - no edit-bike UI;
    - no address edit or delete;
    - no password change;
    - the WhatsApp login button isn't labeled "قريباً". Mark it, or hide it.

    A password change must revoke the other tokens.
11. **Payment methods:**
    - The checkout still offers "كارت بنكي / فودافون كاش / InstaPay" (`config/store.js:31-36`).
    - The cart still lists them.
    - `SettingController.php:37-39` now overwrites the owner's cash trust-item text with fixed text.

    Keep only cash on delivery and pay at the garage, and read the trust text from settings.
12. **Cart:**
    - If the refresh fails (backend down), lines stay as skeletons with no remove button. Show "تعذر تحديث السلة" with a retry, and let the user remove a line using its slug.
    - The alert box icons `alert-triangle` and `info` don't exist in AppIcon. Add them or use existing ones.
13. **Smaller:**
    - remove `role` from `User::$fillable` (review 1, item 2);
    - validate `bikeModel` with `exists:bike_models,code`;
    - the router guard trusts `ayg_auth==='true'` without a token;
    - the duplicate-phone message: use "الرقم ده مسجل قبل كده، سجّل دخول بدل كده";
    - the empty name must not become "عميل الجراج" silently;
    - logout should also clear the cart;
    - the double fetch on page reset is still there, because watchers run after the flag is cleared;
    - the model dropdown shows a year with no bike selected;
    - restrict `allowed_origins` in CORS for production;
    - set a token expiry, for example 60 days.

## Done when

- **Expired login:** put a fake token in localStorage and reload. One 401, the user is logged out, and there are no repeated requests.
- **New account:** register. Orders, bookings and history are empty, including under a new bike.
- **Phone formats:** registering `+20 15…` and `015…` for the same number gives "مسجل قبل كده". Logging in with Arabic digits works.
- **Favorites:** a heart click while logged in saves to `/me/favorites`. A guest favorite survives registering.
- **Max price:** on `/c/oils`, set a max price only; results show.
- **Console:** empty on every route.
- Run `migrate:fresh --seed`, commit, report, then start phase 4 (orders and coupons).
