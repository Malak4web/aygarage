# Backend review 3: review 2 fixes (commits 8d01867..0e4f220)

Reviewed 2026-11-08. Most of review 2 is done.

Tested and working:
- **Bad input:** `perPage=-5`, `perPage=100000`, `page=0`, `q[]=x`, `sort=hack` and `year[]=1` all return 422 in Arabic. None return 500.
- **Reviews:** each product shows only its own reviews.
- **Error states:** with the API failing, every page (product, bike, category, search, bikes, home) shows "إعادة المحاولة", never "غير موجود". Retry works.
- **Hidden items:** sold bikes and inactive products return 404, including the reviews endpoint. Similar bikes are correct.
- **Server filters:** category and bikes send filters, sort and paging to the API and show `meta` and facets. I checked this in the Network tab: `category=oils&model=ZT350-T&brand=Motul&inStock=1&onSale=1`.
- **Home:** loads from `bestSeller`, `weeklyOffer` and `sort=newest`. No index picking is left.
- **Cart:** saves only slug, option code and qty. Old-format carts are cleared once.
- **Other fixes:** `image` / `images`, timezone, `time` column, trust items from settings, README passwords.
- **Mobile:** no horizontal scroll at 375px on any catalog page.

Two bugs are serious, and both must be fixed before phase 3. Paths: `backend/` for PHP, `src/` for Vue.

## A. Must fix

1. **The cart page goes blank after a reload.** I checked this in the browser: add a product, reload, open `/cart`, and the page is empty with `TypeError: Cannot read properties of null (reading 'type')`. Checkout does the same.
   - **Cause:** items come back from localStorage with `product: null`, and `CartPage.vue:40-55` and `CheckoutPage.vue:148-158` read `item.product.type` / `.name` before `refreshCart` finishes.
   - **Fix:**
     - Show a loading skeleton while the cart refreshes.
     - Render a line only when `item.product` is set.
     - Call `refreshCart()` once at app start (`main.js` or `App.vue`), not only on the cart and checkout pages.

     This also fixes the header total and the free-delivery bar, which show 0 after a reload on every page.
2. **With a bike selected, helmets, gear and accessories disappear.** I checked this on the API: `?category=helmets&model=ZT350-T` returns 0, while without `model` it returns 2. CategoryPage turns "يركب على موتوسيكلي" on by default.
   - **Cause:** `ProductController.php:150-153` dropped the universal products. A product with empty or null `fits` fits every bike.
   - **Fix:** `where(fn ($q) => $q->whereJsonContains('fits', $model)->orWhereNull('fits')->orWhereJsonLength('fits', 0))`. Apply the same rule to the facet counts.

## B. Cart refresh (item 18 is not finished)

3. **The "price changed" note never shows after a reload,** which is exactly the case it was made for (added yesterday, price changed today). It compares against `item.product?.price`, which is null after a reload.
   - Save the price the customer saw (`seenPrice`) with each line in localStorage, and compare against it.
4. **Show changes inside the cart, not as toasts.** The toast store has one slot, so with several changes only the last one shows, and an out-of-stock item silently disappears.
   - Show a box at the top of the cart, for example: "تغير سعر تيل فرامل Brembo من 680 إلى 750 ج.م" / "زيت موتول نفد من المخزن واتشال من السلة".
5. **Edits during the refresh are lost.** `refreshCart` builds `newItems` and then overwrites `this.items`, so a remove or quantity change made while it runs is undone.
   - Update each line in place by `lineId`.
   - Reset `isRefreshing` in a `finally`.
6. **A missing option code silently becomes `options[0]`** (`cart.js:143`), which changes the price and can create duplicate `lineId`s. Remove the line and say so in the box from item 4.
7. **One request per cart line.** Add `GET /products?slugs[]=a&slugs[]=b` (or `POST /cart/quote`), validated with a max of 50, and refresh the whole cart in one call. Phase 4 will reuse this when the server computes order totals.

## C. Smaller

8. **No stale-response guard.** On Product, Bike, Category, Search and Bikes, a slow older response can overwrite a newer one when the user changes page or filter quickly.
   - Keep a request counter, or use `AbortController`, and ignore old responses.
   - Debounce the price inputs (about 400 ms).
   - When page ≠ 1, a filter change fires two fetches (the filter watcher plus the page watcher). Reset the page inside the same handler.
9. **SearchPage** sends only `q`: no paging, no facets, and an empty `q` downloads everything. Send `page` / `perPage`, show pagination, and show nothing for an empty `q`. Searching "زيت" finds 3 products, not the 8 oils: also match the category name and the brand.
   - AccountPage still loads the whole catalog with `api.getProducts()` (around :439). Request only what it shows.
10. **ProductPage loading:** show the product as soon as it loads. Load reviews and categories separately, not together in one `Promise.all`. A failed reviews call should show "تعذر تحميل التقييمات", not "no reviews".
11. **Reviews empty state:** NGK shows the tab "التقييمات (97)" and nothing inside it. Show "لسه مفيش تقييمات مكتوبة للمنتج ده". The `(97)` is the star-rating count, so label it like "4.8 من 97 تقييم".
12. **Category page:**
    - The model dropdown shows `· 2023` after every model (`CategoryPage.vue:58` uses `userBikeYear`). Show the year only on the user's own bike.
    - The type facet shows "oil 8" in English. Use the Arabic labels.
    - An unknown category (`/c/xyz`) shows an empty list. Return 404 from the API and show the not-found state.
13. **The "22 موتوسيكل" count:** Home shows "كل الموتوسيكلات (22)" and the bikes page says "22 موتوسيكل", but only 6 are for sale. `totalInspectedBikes` is a marketing number. Use `meta.total` from `/bikes` for these labels. If he wants to keep it, word it as "فحصنا أكتر من 22 موتوسيكل".
14. **Payment wording:** the trust item "أو كارت وفودافون كاش" and the cart's "كارت بنكي · فودافون كاش · InstaPay" promise payments that don't exist. The brief says cash on delivery or pickup only. Remove them or make them come from settings.
15. **Validation:**
    - `brand`, `viscosity`, `type`, `family` and `year` as plain values have no rule, so `?year=abc` is treated as "2021 وأقدم". Accept either a string or an array, and validate both.
    - Add `maxPrice >= minPrice`.
    - The attribute name `year.0` shows in English. Add `year.*` and the other `.*` names to `lang/ar/validation.php`.
16. **Bikes:**
    - The "80-89" score option sends `minScore=80`, so the 90+ bikes show too. Send `maxScore` as well.
    - The year options are fixed in code. Build them from `facets.years`.
    - The bike model filter still uses `like '%…%'` (`BikeController:28`). Use an exact match.
17. **Request helper:**
    - Redirect on 401 only when the current route has `requiresAuth`. On public pages, just clear the token.
    - Handle a 204 / empty body without calling `response.json()`.
18. **Cleanup:**
    - unused `use Illuminate\Http\Request` in both controllers;
    - `doesFit` in CategoryPage and `onMounted` in ProductPage;
    - unreachable inner error blocks (CategoryPage:168, BikesListingPage:153);
    - the redundant clamps;
    - the `'p1'` in `HomePage.vue:301`;
    - `src/data/{bikes,categories,products,reviews,services}.js`: delete them if nothing imports them;
    - add `orderBy` to `related` / `similar`;
    - write the "sold bike = 404" decision in the README;
    - the error text "تعذر الاتصال بقاعدة البيانات" is technical. Use "تعذر التحميل، اتأكد من النت وحاول تاني".

## D. Notes for phase 3

- Favorites still use the old ids (`p3`). Phase 3 moves them to the server keyed by product slug, and migrates the localStorage favorites into the account on first login.
- `POST /sell-requests` with the sell modal, as in review 2 D.
- `401` from `/me` must log the user out cleanly (item 17).

## Done when

- **Cart:** add a product, reload, then open `/cart` and `/checkout`: both render, with no console error. The header shows the right total on every page.
- **Price note:** change a product's price in the DB, then reload: the cart shows the note with the old and new price.
- **Universal products:** with a bike selected, `/c/helmets` shows the helmets.
- **Console:** empty on every route.
- Commit, report, then start phase 3 (auth, account, sell requests).
