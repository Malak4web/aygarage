# Review round 2: fixes required

Reviewed 2026-11-07 after commits 573cb5c..1f39bda. Good progress: 19 of 40 items fully fixed, mobile overflow is gone (no horizontal scroll at 375px on any route), login guard + redirect works, out-of-stock works, old price shows, the fake discount and seeded cart are gone.

**But nobody can buy right now.** Two runtime crashes break the cart and checkout. `npm run build` does not catch them, so after fixing, open every page in the browser and check the console is empty.

Paths are under `src/`.

## A. Crashes: fix first

1. `pages/CheckoutPage.vue:232` imports `ref, computed, onMounted` but line 262 calls `watch(...)`. The page throws `ReferenceError: watch is not defined` and renders nothing. Add `watch` to the import.
2. `components/common/OrderSummary.vue:23` uses `cartStore.remainingForFreeDelivery`, which does not exist (the getter is `freeDeliveryRemaining`, `stores/cart.js:50`). The summary crashes whenever subtotal < 3,000, so the cart page has no summary and no "إتمام الشراء" button. Use the right name.
3. Then run the full guest flow in the browser and confirm it works: add to cart → cart → checkout → login → back to checkout → confirm → `/order/:id` shows items and totals → order appears in Account › طلباتي.

## B. Logic bugs

4. Checkout must use `OrderSummary` too (CheckoutPage:273-280 duplicates delivery/total logic). One source of truth in the cart store.
5. ProductCard "أضف للسلة" on a product with size options adds it with no option, creating a different cart line (`p1_default`) from the product page (`p1_1l`). Add the default option (`options[0]`).
6. Home hero button adds the Motul product to the cart on every slide, including "تسوّق الزيوت" and "احجز فحص فوري". Each slide needs its own action: link to category, link to booking, or add its own kit.
7. CategoryPage: the "fits my bike" filter is `true` on first load but `false` after a category change (script L67 vs L162/176). Make it consistent (default `true` when a bike is selected).
8. Booking from a bike page: `?bike=Zontes ZT350-T` doesn't match option values like `ZT350-T`, so the select is blank (BookingPage:281). Pass the model code. The login redirect from booking (BookingPage:297) and checkout must use `route.fullPath`, not a fixed path, so the query survives.
9. Booking submit is fake. Call `api.createBooking`, save it, and show it in Account › حجوزاتي.
10. `/order/:id` with an unknown id only logs a warning. Show a not-found state.
11. Cart quantity stepper must be capped at the product's stock.
12. Carts saved before `lineId` existed break remove/qty. On load, add `lineId` to old items or drop them.
13. `FilterSidebar.vue` renders its slot twice (desktop aside + mobile sheet), so every filter input exists twice. Render the sheet with `v-if` only when open, or render one copy that changes layout.

## C. Visual: match `design-concepts/shop.html`

14. **RTL side flips.** Several `left:`/`right:` were converted to logical properties on the wrong side. In this RTL site `inset-inline-start` = right and `inset-inline-end` = left. Put each element back where the design has it:
    - Home hero bike art: on the **left** (design has `left:30px`): use `inset-inline-end`. Today it sits under the text and the "احجز التركيب" button.
    - Home side banners (`.ban.s .art`): art on the **left**. Today the helmet and bike cover the titles and links on desktop and phone.
    - Hero dots: on the **right** under the text (design `right:56px`).
    - ProductCard: discount/new badge **top-right**, heart button **top-left**.
    - Checkout mini qty badge: top-left of the thumbnail.
15. Bike detail "similar bikes" row: pass `columns=3` (3 bike cards sit in a 5-column grid).
16. Product sticky buy bar still covers the bottom of the footer on phone. Add bottom padding to the page body/footer when the bar is visible.
17. CartPage:58 neutral fit text must match exactly: "مش متأكدين إنه يركب على موتوسيكلك، اسألنا".

## D. States and data still missing

18. Loading/error/retry: add to Home (HomePage:236 only `console.error`), Search, Booking (services) and Account. Load through `api/`.
19. BikesListing km/score/year facet counts are hard-coded (script L259-275). Compute them.
20. Still hard-coded: "2-4 أيام" and "14 يوم" on OrderSuccess:57/61; `storeConfig.deliveryEstimate` is a fixed date ("الخميس 12 نوفمبر"). Compute the delivery date from today + config days.
21. "كريم" fallback still in AppHeader.vue:29. Show "تسجيل الدخول" for guests.
22. `RadioCard` and `FormField` were created but are not used anywhere. Use them in Checkout, Booking, Login and Account, or delete them.
23. HeroCarousel has no prev/next buttons. Add them, plus autoplay that pauses on hover/focus.

## E. Accessibility

24. Focus rings still removed: scoped `outline: none` beats the global `:focus-visible`. Fix in CartPage:349, CheckoutPage:456/478, SearchBar:81/97, AppFooter:172, HomePage:453. Use `:focus-visible { outline: 2px solid var(--ink); outline-offset: 2px }` in each, or drop the `outline:none`.
25. Labels without `for`: CheckoutPage:40/50/85, Account settings and add-bike modal. Using `FormField` (item 22) fixes this.
26. `ProductIllustration.vue:5` still falls back to the type ("oil") for `alt`. Pass the product name from Product, Cart and Checkout.
27. Modals (Product bike picker, Account add-bike, Bikes filter sheet, FilterSidebar sheet): add Esc to close, a focus trap and body scroll lock. Drawer needs a focus trap.
28. Remaining physical CSS: CartPage:250, BikesListingPage:490, CategoryPage:607.

## Definition of done for this round

- Browser console is empty on every route (open each one, not just build).
- The full guest flow in item 3 works end to end, and a booking shows up in Account.
- Desktop home, product card and checkout match `shop.html` positions (item 14).
- Report back per section A to E with what changed.
