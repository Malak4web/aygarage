# Review round 1: fixes required

Reviewed 2026-11-07 against `BUILD_BRIEF.md` and `design-concepts/shop.html`. `npm run build` passes and desktop pages look close to the design. The problems are logic bugs, broken mobile layout, and missing states. Fix in the order below and commit per section. Paths are under `src/`.

## 1. Critical: blocks launch

1. **Mobile layout is broken on every page.** `components/layout/CategoryNav.vue`: `.catnav-inner` has `white-space: nowrap` and no overflow handling, so on phones the category bar is ~725px wide and stretches the whole page. Everything is pushed sideways; the product page looks blank on a 390px screen. Fix: at ≤992px hide the category bar (categories live in the drawer, per brief) or make `.nav-links` `overflow-x: auto`. Then verify at 375px that `document.documentElement.scrollWidth === innerWidth` on every route.
2. **Every visitor starts logged in.** `stores/user.js:7` `isAuthed = true`. Default to `false`; remove unused `returnUrl`; deep-clone `defaultUser` (`structuredClone`) so register/addBike don't mutate the mock module; clear user on logout.
3. **No login guards.** `router/index.js` has no `meta.requiresAuth` / `beforeEach`. Add them for `/account`; checkout confirm and booking confirm must check auth and send to `/login?redirect=<fullPath>`; `LoginPage` already reads `redirect`.
4. **Fake 710 EGP discount always applied.** `stores/cart.js:64` `couponDiscount: 710` and `applyCoupon` falls back to 710 for any invalid code (line 166). Default 0; invalid code shows an error; make discount a getter computed from `couponCode` + `subtotal` (so it updates with qty); persist the coupon.
5. **Cart refills itself with demo items** whenever it's empty (`stores/cart.js:16-59`). Delete the seed. Empty cart must show `EmptyState`.
6. **Cart lines collide by product.** `removeItem`/`updateQty` and `:key` use `productId`, but lines are split by size option. Give each line a `lineId` (`productId + optionId`) and use it everywhere (`CartPage.vue:35`, `CheckoutPage.vue:172`).
7. **Product page keeps the previous product's size/price.** `pages/ProductPage.vue:316-323`: set `selectedOption = newP.options?.[0] ?? null`, reset `activeTab`, `activeThumb`, and quantity to **1** (it defaults to 3 on every product, also in HomePage hero add, line 250).
8. **Category listing shows wrong results.** `pages/CategoryPage.vue:255-261` pre-applies Motul + 10W-40 + in-stock on every category; `/c/oils` doesn't filter by category (line 323). Defaults must be empty except the selected-bike fit filter; always filter by `route.params.category`; build filter groups and counts from products actually in that category (viscosity/brand/type are oil-only today); reset filters and page when the category changes and reset page when filters change.
9. **Search goes to `/c/oils?q=`** (`components/layout/SearchBar.vue:38`) and inherits those filters, so "Brembo" returns nothing. Add a `/search?q=` route that searches all products.
10. **Product rows have no grid outside Home.** `.grid`/`.sec`/`.sh` exist only in `HomePage.vue` scoped CSS, so "بيتشترى معاه" (Product), "ممكن تحتاج كمان" (Cart), similar bikes (Bike detail) and Account render one giant card per row. Create a `ProductRow` component (5/3/2 columns) and use it on all pages.
11. **Order success page is empty.** `/order/:id` only shows the id; the order is dropped (`CheckoutPage.vue:291-293`). Store orders (Pinia `orders` store or `api.getOrder`), render items + totals on the success page, and show the order in Account › طلباتي.
12. **Out-of-stock is never handled.** Add one product with `stock: 0` to `data/products.js`. On Product page disable both buy buttons and the sticky bar and show "غير متوفر"; cap `QtyStepper` at `stock`.

## 2. Visual mismatches with the design (desktop)

13. Home hero: the bike illustration overlaps the "احجز التركيب" button. Put the text block above the art (`z-index`/`position: relative`) or move the art further left.
14. Product page: the old price and saving ("1,650 · وفّر 200 ج.م") disappear because `currentOldPrice` returns 0 when any option is selected (line 330). Add `oldPrice` per option and show it.
15. Product page rating renders "4.8 () 126 تقييم" with empty parentheses. Remove them.
16. Fit box: year "· 2023" is hard-coded (ProductPage 63, CategoryPage 93, BookingPage 49) and the oil-only note "محتاج 3 لتر…" shows on every product. Use the saved bike's year and a per-product note from data. Neutral text must be exactly: "مش متأكدين إنه يركب على موتوسيكلك، اسألنا".
17. Unknown product/bike slug silently shows the first item (`ProductPage.vue:301`, `BikeDetailPage.vue:164`). Show a not-found state.

## 3. Major: behaviour and states

18. One `doesFit(product, bike)` helper used by FitBadge, ProductPage, CategoryPage and CartPage (today they disagree; empty bike makes everything "fit"). Cart lines must show the real fit, not always "يركب على …".
19. Load data through `api/` on every page with loading (use the existing unused `.skeleton` CSS for card skeletons), error with retry (`EmptyState`), and empty states. Today only Checkout and Booking use the API.
20. Bikes listing: km and price filters do nothing; store `km` as a number; implement ranges; "2021 وأقدم" must mean ≤2021; add the phone filter bottom sheet (sidebar is just hidden ≤900px).
21. Sorts "الأكثر مبيعاً" / "الأحدث" do nothing; add `soldCount` / `createdAt` to data.
22. Hard-coded listing UI: "عرض 1-12", facet counts, breadcrumbs fixed to زيوت. Compute them.
23. Home finder sends `?model&year` but CategoryPage ignores both. Read them.
24. Checkout: redirect to cart when cart is empty; show an error message on failure (not `console.error`); send numbers, not formatted strings.
25. Booking: use the user's saved bikes (not a hard-coded plate); "no bikes" state; generate the next 7 days from today instead of fixed November 2026; submitting/error states; "احجز معاينة" on bike detail should pass the bike.
26. Account: add the "حجوزاتي" tab; add a logout button; settings must use `v-model` and save to the store (no `alert()`); empty state when no bikes; clamp the service meter 0-100%.
27. Move hard-coded prices/policies into `config/store.js` or `data/`: hero kit price (HomePage 227), service prices duplicated in HomePage 281-286, "2-4 أيام"/"14 يوم" (HomePage 289/292), count "22" (HomePage 51/157, BikesListing 8), installment "10,200" (BikeDetail 33), payment list (CartPage 134, AppFooter → `storeConfig.paymentMethods`), governorates (Checkout 64-80), reviews (ProductPage 204-217).
28. Extract the missing shared components and remove duplication: `FilterSidebar` (+ bottom sheet, reused by bikes listing), `FilterChips`, `SortSelect`, `HeroCarousel` (autoplay pause on hover, prev/next, swipe), `ProductRow`, `Tabs`, `RadioCard`, `FormField`, `OrderSummary` (Cart and Checkout currently compute delivery differently; keep the logic in the cart store).
29. Replace every `alert()` (Login 143, Account 369/373, BikeDetail 193, BikesListing 212) with inline UI or a toast. Remove the pre-filled login password and hard-coded "كريم محمود" fallbacks.

## 4. Mobile and tablet

30. Phone header: per brief, logo + search icon + cart + hamburger (search opens on tap). Drawer must close on any route change (watch the route in `App.vue`).
31. Cart page ≤900px: the remove X escapes to the page corner; add `position: relative` to `.line`.
32. Product sticky buy bar: show it ≤768px (not only ≤600px), add bottom padding to the page so it doesn't cover the footer, use `inset-inline: 0`.
33. Home grid columns must be 5 → 3 at 768px → 2 on phone.

## 5. Accessibility and RTL

34. Replace physical CSS (`left/right`, `margin/padding-left/right`, `text-align: left/right`, inline `style="text-align:left"`) with logical properties. Main offenders: ProductCard 146/163, BikeCard 91, SearchBar, Checkout, CategoryPage, BikesListingPage, HomePage, CartPage, BikeSelector, AccountPage inline styles.
35. Don't remove focus rings: every `outline: none` needs a `:focus-visible` style; hidden radio/checkbox inputs need a visible focus on their card (`.rad:has(input:focus-visible)`).
36. Service cards, day slots, time slots (Booking) and bike thumbnails must be `<button>`s with `aria-pressed`, not divs.
37. Every input needs a linked `<label for>`; add labels to coupon, sort selects, model select, booking odometer, newsletter email.
38. Images: `alt` = product/bike name (ProductIllustration uses the type, e.g. "oil").
39. Drawer, filter sheet and modals: `role="dialog"`, `aria-modal`, Esc to close, focus trap, body scroll lock; closed drawer must not be in the tab order (`v-if` or `inert`).
40. RatingStars: draw partial stars and add an `aria-label` ("التقييم 4.8 من 5"); Pagination `aria-current="page"`; WhatsApp link `rel="noopener"`.

## Definition of done for this round

- At 375px, 768px and 1280px: no horizontal scroll on any route.
- Guest flow works: browse → add to cart → checkout asks to log in → returns to checkout → order success shows the order → it appears in Account.
- Empty cart, no search results, out-of-stock product and not-found product all show proper states.
- `npm run build` passes with no console errors or warnings in the browser.
- Report back per section with what changed.
