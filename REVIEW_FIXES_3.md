# Review round 3: fixes required

Reviewed 2026-11-07 after commits 45563a5..65df8fb. Good work: the site now works end to end. The browser console is empty on all routes, nothing scrolls sideways at 375px, the guest flow works, and bookings show up in Account.

Tested in the browser:
- add to cart → cart → checkout → login → back to checkout → confirm → `/order/:id` → Account › طلباتي
- booking → Account › حجوزاتي

21 of 28 items from round 2 are fully fixed. The list below is short and mostly small. Paths are under `src/`.

## A. Bugs

1. **Booking cost is always empty.** `pages/BookingPage.vue:177` and `:329` read `selectedService.estimatedCost`, but `data/services.js` only has `price` / `priceFormatted`. Because of this:
   - the summary shows "—";
   - bookings are saved with no cost;
   - the التكلفة column in Account › حجوزاتي is blank.

   Show `priceFormatted`. Save the numeric `price` and format it in Account.
2. **Hero prev/next buttons are blank.** `components/common/HeroCarousel.vue:20,28` use `AppIcon name="right"` / `"left"`, which don't exist in `AppIcon.vue`. Add chevron icons. Also add the missing `filter` icon used by the filter buttons in CategoryPage and BikesListing.

   The buttons have `opacity: 0` until hover or focus, so phone users never see them. On touch / ≤768px, keep them visible or rely on swipe.
3. **Order not-found text is lost.** `pages/OrderSuccessPage.vue:11` passes `desc=`, but EmptyState's prop is `description`.
4. **Checkout login redirect is fixed.** `pages/CheckoutPage.vue:248` uses `redirect: '/checkout'`. Use `route.fullPath`.
5. **Adding to cart ignores stock.** `stores/cart.js:103` `addItem` does `existing.qty += qty` with no stock cap. Clamp it to `stock` and show a toast when the cap is hit.

## B. Data still hard-coded

6. Delivery days:
   - `config/store.js:67` adds a fixed `+ 3` days. Put the number of days in config.
   - `trustItems` (store.js:56-59) still has "2-4 أيام عمل" and "14 يوم" typed in. Build them from the config values.
   - The order success page says "خلال 2-4 أيام عمل", but checkout shows a real date ("الثلاثاء 10 نوفمبر"). Show the same computed date on both pages.
7. Booking still imports `@/data/services.js` (BookingPage:203, :225-226, :357). Load services only through `api/`.
8. The Account service history is hard-coded in `pages/AccountPage.vue:471-473`. Move it to `data/` and load it through `api/`.
9. After login, the header and Account always show "كريم محمود", because `login()` clones `defaultUser` (stores/user.js:92). At minimum, use the phone number that was entered. Registration must use the entered name.

## C. Visual

10. **Phone, home side banner 2:** at 375px the bike drawing overlaps the title "موتوسيكلات مفحوصة". Fix one of these ways:
    - shrink or move the art;
    - reserve space for the art with `padding-inline-end` on the text.

    The desktop hero overlap matches `shop.html`, so leave it.
11. **Product page tabs (desktop):** `components/common/Tabs.vue:41` has `overflow-x: auto`, and Windows draws a small scrollbar with arrows at the left end of the tab bar. Hide it:

    ```css
    scrollbar-width: none;
    ::-webkit-scrollbar { display: none }
    ```
12. **Booking digits:** the month label shows Arabic-Indic digits ("نوفمبر ٢٠٢٦") while the rest of the site uses Latin digits. Format dates with Latin digits, e.g. `ar-EG-u-nu-latn`.

## D. Small logic / UX

13. Bikes listing facet "80 – 89": the count excludes 90+, but the filter is `>= 80` (BikesListingPage:400). Make the count and the filter match.
14. Category "مسح الكل" can't clear the "fits my bike" chip, because reset puts it back to `true`. "مسح الكل" should clear it; the default `true` should apply only on first load and on category change.
15. Booking is locked for guests on entry. The brief says only **confirm** needs login. Let guests:
    - see the booking page;
    - pick a service and a time.

    On "تأكيد الحجز", send them to `/login?redirect=<fullPath>`. Keep their choices in the query or sessionStorage so they come back to the same selection.

## E. Accessibility

16. Checkout and Booking `RadioCard`s have no `name`, so the options aren't grouped and arrow keys don't move between them. Pass a `name` for each group: `delivery`, `payment`, `service`.
17. Decorative illustrations on Home (HomePage.vue:34, 106) get `alt="helmet"` from the type fallback. Pass `alt=""` where the image is decorative, and drop the `alt || type` fallback in `ProductIllustration.vue`.
18. `utils/modal.js`: if the window is resized to desktop while a filter sheet is open, the sheet unmounts but the body stays scroll-locked. Close the sheet (set `isOpen = false`) when switching to desktop.

## F. Housekeeping

19. `package.json` now has a `puppeteer-core` devDependency, and there are `scripts/verify_section_*.mjs` files. They are not part of the site. Ask the owner before keeping them. If they stay, list them in README under "dev scripts".

## Definition of done

- Browser console stays empty on every route.
- A booking shows its cost in the summary and in Account › حجوزاتي.
- Hero prev/next and the filter buttons show their icons.
- Home looks right at 375px, with no banner art over text.
- Report back per section A to F.
