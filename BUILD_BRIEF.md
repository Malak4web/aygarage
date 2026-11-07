# Build brief: جراج أبو يوسف (Abu Yousef Garage) storefront

You are building the first version of the website as a Vue 3 app. The approved design already exists. Your job is to turn it into a real, componentized, responsive Vue app. Do not redesign anything.

## Source of truth

- **Visual design:** `design-concepts/shop.html`. Open it in a browser. It contains 9 page mockups (switch them with the tabs at the top). Every layout, spacing, color, font, label and piece of Arabic copy comes from that file. When this brief and the file disagree on looks, the file wins.
- **Product context:** `PRODUCT.md`.
- Ignore every other file in `design-concepts/`. Those are rejected directions.

## Stack

- Vue 3 (Composition API, `<script setup>`), Vite, Vue Router, Pinia.
- Plain CSS with CSS custom properties (one `src/styles/tokens.css` + scoped component styles). No Tailwind, no UI kit.
- Font: Almarai from Google Fonts (weights 300, 400, 700, 800).
- No backend yet. All data comes from local mock files in `src/data/`. Write it so a real API can replace the mock layer later (one `src/api/` module that returns Promises).
- Package manager: npm. `npm run dev` must start the site.

## Global rules

- Arabic, RTL: `<html lang="ar" dir="rtl">`. Use logical CSS properties (`margin-inline-start`, `padding-inline`, `inset-inline-start`) so nothing breaks in RTL.
- Prices in EGP, formatted with `toLocaleString('en')` and `ج.م`, e.g. `1,450 ج.م`. Use `font-variant-numeric: tabular-nums`.
- Responsive: the mockups are desktop (1280px). You must also make it work at tablet (768px) and phone (375px):
  - Header collapses to logo + search icon + cart + hamburger; category nav becomes a slide-in drawer.
  - Product grids: 5 cols desktop → 3 tablet → 2 phone.
  - Listing filters become a "فلترة" button opening a bottom sheet on phone.
  - Product page gallery stacks above the buy box; the add-to-cart bar sticks to the bottom on phone.
  - Cart/checkout summary moves below the lines on phone.
- Accessibility: real `<button>`/`<a>` elements, visible focus ring, `alt` text on images, labels on every form field, color contrast at least 4.5:1.
- Images: use placeholder images for now (a neutral `#f6f6f6` box with the product name is fine), but build the components to take a real `image` URL from the data.

## Design tokens (copy from shop.html `.S` block)

```
--bg: #ffffff;  --sf: #f6f6f6;  --sf2: #eeeeee;
--ink: #121212; --mu: #6b6b6b;  --ln: #e7e7e7;
--sale: #c62828; --salesoft: #fdecec;
--ok: #1b7f4b;  --oksoft: #e8f4ee;  --star: #f2a516;
radius: 10px (buttons, inputs), 14px (cards), 16px (banners/boxes)
button height: 46px (default), 38px (small)
page side padding: 48px desktop, 16px phone
```

Red is only for discounts, sale badges and the "العروض" link. Primary buttons are black (`--ink`) with white text.

## Pages and routes

| Route | Page | Notes |
|---|---|---|
| `/` | Home | announcement bar, header, category nav, hero banner (carousel, 3 slides) + 2 side banners, "دوّر على قطع موتوسيكلك" finder, category circles, best sellers row, weekly offers row, used bikes row, service booking block, trust bar, footer |
| `/c/:category` | Category listing | breadcrumb, active filter chips, filter sidebar (model, viscosity, brand, type, price range, in stock, on sale), sort, grid, pagination |
| `/p/:slug` | Product | gallery with thumbs, brand + SKU, title, rating, price + old price + saving, **fit box** (green when the item fits the user's selected bike), size options, qty, add to cart, buy now, stock line, delivery/pickup/returns perks, tabs (description, specs, reviews, compatible models), "بيتشترى معاه" row |
| `/cart` | Cart | lines with qty stepper and remove, free-delivery notice, summary with coupon, checkout button, "ممكن تحتاج كمان" row |
| `/checkout` | Checkout | minimal header, 3-step indicator, delivery address form, delivery method (home / garage pickup), payment method (cash on delivery, card, Vodafone Cash, InstaPay), order summary, confirm button. On confirm: go to an order-success page `/order/:id` (simple: order number, summary, "تابع طلبك" button) |
| `/bikes` | Used bikes listing | filters (model, price, km, inspection score, year), sort, grid of bike cards with inspection score badge |
| `/bikes/:slug` | Bike detail | gallery, inspection score tag, title, km/owner/license line, price + installment line, "احجز معاينة" + WhatsApp buttons, spec table, tabs (inspection report with per-section bars, description, service history), Abu Yousef notes box, similar bikes |
| `/booking` | Maintenance booking | 1) choose bike (saved or new), 2) choose service, 3) day + time slot; sticky summary with confirm. On confirm show a success state |
| `/account` | Account | sidebar (my bikes, service history, bookings, orders, favorites, addresses, settings), my bike card with odometer and next-service bar, service history table, recent orders table |
| `/login` | Login / register | not in the mockup: build it in the same style. Phone number + password, tab to register (name, phone, password, optional bike model), and "ادخل بكود على الواتساب" secondary button |

Account, booking confirm and checkout confirm require login: redirect to `/login` and come back after.

## Components to extract (minimum)

`AppHeader`, `AnnouncementBar`, `CategoryNav`, `MobileDrawer`, `AppFooter`, `SearchBar`, `BikeSelector` (the "موتوسيكلي" picker in the header + finder), `ProductCard`, `BikeCard`, `PriceTag`, `RatingStars`, `FitBadge`, `QtyStepper`, `FilterSidebar`, `FilterChips`, `SortSelect`, `Pagination`, `Breadcrumbs`, `HeroCarousel`, `ProductRow`, `Tabs`, `RadioCard`, `FormField`, `OrderSummary`, `EmptyState`.

## State (Pinia)

- `cart`: items, add/remove/update qty, subtotal, discount, delivery fee (60 ج.م, free over 3,000), total. Persist in `localStorage`.
- `user`: logged-in user, saved bikes, selected bike. The selected bike drives every "يركب على …" fit badge and the default filter on listings.
- `favorites`: product ids. Persist in `localStorage`.

## Mock data (`src/data/`)

Use the products, bikes, services, service history and orders that appear in `shop.html` (names, brands, prices, old prices, ratings, review counts). Each product needs: `id, slug, name, brand, sku, category, price, oldPrice?, rating, reviewCount, stock, fits: [modelCodes], image, specs{}, description`. Each bike needs: `id, slug, model, year, km, price, owner, licenseUntil, inspectionScore, inspection: [{section, score, note}], notes, images`. Zontes models: `ZT125-U, ZT310-R, ZT310-X, ZT350-T, ZT350-GK, ZT350-D, ZT703`.

All prices, ratings, delivery times and return policy text are placeholders. Keep them in the data files or one `src/config/store.js`, never hard-coded inside components, so the owner can replace them.

## States you must handle

Loading (skeleton cards), empty (empty cart, no filter results, no bikes saved), error (failed load with retry), out-of-stock product (disabled add-to-cart, "غير متوفر"), item that does not fit the selected bike (neutral fit box: "مش متأكدين إنه يركب على موتوسيكلك، اسألنا").

## Folder structure

```
src/
  api/          mock API (Promise-based)
  assets/
  components/   shared components
  config/       store settings (delivery fee, free-delivery threshold, phone, payment methods)
  data/         mock JSON/JS
  pages/        one file per route
  router/
  stores/       Pinia stores
  styles/       tokens.css, base.css
  App.vue
  main.js
```

## Definition of done

1. `npm install && npm run dev` runs with no errors or console warnings.
2. All 10 routes above work and look like `design-concepts/shop.html` at 1280px.
3. Add to cart → cart → checkout → order success works end to end with mock data.
4. Selecting a bike in the header changes the fit badges and listing results.
5. Works at 375px and 768px with no horizontal scroll.
6. `npm run build` succeeds.
7. Write a short `README.md`: how to run, where the mock data lives, what must be replaced with real data.

Work in small commits per area (layout shell, home, listing, product, cart/checkout, bikes, booking, account, responsive pass). Stop after each area and report what you did, so it can be reviewed.
