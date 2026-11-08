# Backend review 2: phase 2 (commits 1fbd1a2, f9bb853)

Reviewed 2026-11-08. All 14 items from review 1 are done; only `intl` remains, and the owner is enabling it.

Phase 2 works end to end:
- All public pages load from MySQL through the Vite proxy, with an empty console.
- I changed a product's price and stock in the DB, and the site showed the new price and "غير متوفر" at once.
- Filters, sorts, paging, facets, the 404s and the Arabic messages all work on the API.

But the frontend does not use most of what the API offers yet, and a few API edges crash. Fix these before phase 3. Paths: `backend/` for PHP, `src/` for Vue.

## A. Bugs

1. **Every product shows the Motul reviews.** `ProductPage.vue:350` calls `api.getReviews()` without the product, and `api/index.js:94` defaults to `motul-7100-10w40`. Pass `product.slug` and remove the default and the `'p1'` mapping.
2. **A network failure shows "غير موجود".** I checked this in the browser: with fetch failing, `/p/...` and `/bikes/...` say the product or bike doesn't exist.
   - Show the not-found state only for a 404. For any other error, show the error state with "إعادة المحاولة".
   - `ProductPage` uses `Promise.all`. Load the product first, then load reviews and related products separately, so a failed reviews call doesn't hide the product.
   - Category on failure shows "جميع المنتجات · 0 منتج". Show the error state instead.
3. **API crashes (500):**
   - `?perPage=-5` gives a SQL error, and `perPage=100000` is accepted. Validate and clamp `perPage` to 1-48, and `page` to ≥ 1 (ProductController:162, BikeController:122).
   - `?q[]=x` gives a TypeError at ProductController:28.

   Validate every query parameter with a FormRequest. Any bad input must return 422 in Arabic, never 500.
4. **Images will never show.** The API returns `images: []`, but the components read `image` (ProductCard:28, BikeCard:11, ProductPage:50/60, CartPage:42, CheckoutPage:150). Add `image` (the first image's full URL, or null) to both resources, and use `images` for the product and bike galleries.
5. **Order changed, so the site no longer looks the same.** With no sort, `/products` now returns best-sellers first and `/bikes` newest first. The pages pick items by position: `HomePage.vue:331-345` with indexes `[0],[2],[4],[9],[7]`, plus CartPage:189, ProductPage:357 and AccountPage:480.
   - Home should ask the API for what it shows: `bestSeller=1`, `weeklyOffer=1`, and bikes `sort=new&perPage=4`.
   - Never pick by index. Default order when no sort is given: `id asc`.
6. **Sold bikes and inactive products can be reached directly.**
   - `bikes/{slug}` (BikeController:151) serves sold bikes. Return 404 for sold bikes, or show "تم البيع" and hide the booking button. Pick one and document it.
   - `products/{slug}/reviews` doesn't check `is_active`.
7. **Similar bikes:** `family = X OR id != bike` matches every bike (BikeController:162-169). Show the same family first, then fill to 3 with other bikes, excluding the current one. Eager-load `serviceRecords`.
8. **Bike history:** a bike with no history now shows an empty table with headers (BikeDetailPage:130 still has the `defaultHistory` fallback). Remove the fake fallback and add an empty state: "مفيش سجل صيانة مسجل للموتوسيكل ده".

## B. Use the server, not the browser

9. **Catalog pages don't use the server filters yet.** These pages fetch every product or bike and filter in the browser, and ignore `meta` and `facets`:
   - CategoryPage (around line 290);
   - SearchPage (line 94);
   - BikesListingPage (line 221 onward).

   Change them to send the filters, sort, `page` and `perPage` to the API, and render `meta` (counts, "عرض 1-12 من …", pagination) and `facets` from the response.

   This only works with 16 products today. With hundreds, the site would download everything on every page.
10. ProductPage and BikeDetailPage rebuild related and similar items from the full list. Use `related` and `similar` from the API response.
11. **Model filter:** `like '%model%'` (ProductController:38-45) makes `ZT350` match every ZT350 variant. Use an exact match: `whereJsonContains('fits', $model)`.
12. **Bikes year filter:** "2021 وأقدم" means `year <= 2021`; today it is an exact match.

## C. Before phase 3

13. **Request helper** (`api/index.js:6-42`):
    - on 401, clear the user store and redirect to `/login?redirect=<current fullPath>`;
    - turn 422 responses into `{ field: firstMessage }` for the forms.
14. **Timezone:** set `APP_TIMEZONE=Africa/Cairo` in `.env.example`, and make `config/app.php` read it. The delivery date and the booking "past slot today" check depend on it.
15. `bookings.time`: change it to a `time` column (`HH:MM`, 24h) and format it in Arabic in the resource (review 1, item 7).
16. **Trust items:** build "14 يوم" and "2-4 أيام" from `returnDays` / `deliveryEstimateDays` in `SettingController`, not as fixed text. HomePage `heroSlides` must react to settings loaded after start (use a `computed`).
17. **README:** remove the sample passwords (`admin123456` / `123456`) and tell the reader to choose their own.
18. **Old carts:** the cart keeps a full copy of each product (price, stock) from when it was added. Store only `slug`, option `code` and `qty`, and refresh price and stock from the API when the cart and checkout pages open. Show a note when a price changed or an item went out of stock. Clear carts in the old format once.
19. In `lang/ar/validation.php`, add attribute names for `bikeModel`, `productId`, `optionCode`, `items`.

## D. Moved to later phases (no action now)

- `GET /booking/slots` → phase 5.
- `POST /coupons/check` → phase 4.
- `POST /sell-requests` → phase 3, together with the sell modal. Validate every field. Accept only name, phone, model, year, km and notes, and rate-limit it.

## Done when

- `/p/<any product>` shows only that product's reviews, or the empty state.
- Category, search and bikes send filters to the API: check the browser Network tab for `?category=…&brand[]=…`.
- With the backend stopped, every page shows an error with "إعادة المحاولة", never "غير موجود".
- `?perPage=-5` and `?q[]=x` return 422.
- Home shows the same products and bikes as before the API.
- Commit, report, then start phase 3.
