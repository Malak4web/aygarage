# Review round 4: final small fixes

Reviewed 2026-11-07 after commits 78729d3..541df03. The site works.

What was tested in the browser:
- The console is empty on every route.
- Nothing scrolls sideways at 375px.
- Guest purchase flow, end to end.
- Guest booking: the choices are restored after login, and the cost (900 ج.م) shows in the summary and in Account › حجوزاتي.
- Stock cap: the cart stops at 24, the stock.
- Checkout and the order page show the same delivery date.
- The hero arrow and filter icons render.
- The phone banners no longer overlap their text.

15 of 19 items from round 3 are fully fixed. What is left is small. Paths are under `src/`.

## A. New users get the demo customer's data

1. `stores/user.js:74-99`: `login()` and `register()` clone `defaultUser`, so every new user gets Karim's data: email, address, saved bike, orders and service history. A new phone number should start with:
   - the entered name and phone;
   - no saved bikes, addresses, orders or history.

   That way the empty states show. Keep the full demo data only for the demo phone number.
2. Because of item 1, checkout opens with Karim's address already filled in for a brand-new user. Fill the address only from the user's own saved addresses.
3. `AppHeader.vue:29` shows the first word of the name, so phone logins read "أهلاً عميل". Show the name only when the user typed one; otherwise show "حسابي".
4. `AccountPage.vue:145, 227` render `user.serviceHistory || serviceHistoryList`, so the history from `api.getServiceHistory()` is never shown. Use the api list only, and remove `serviceHistory` from `data/user.js`.

## B. Cart and booking

5. `ProductPage.vue:407` shows "تمت إضافة… بنجاح" right after `addItem`, which hides the stock-limit toast. Show the success toast only when the item was actually added. `addItem` should return true or false.
6. `stores/cart.js` `addItem`: when the product has `stock <= 0`, add nothing; today it creates a qty 0 line. `HomePage.vue:295` (hero action) must respect stock too.
7. `BookingPage.vue`: the saved guest choice (`ayg_pending_booking`) is removed only after a successful booking. Remove it once it has been restored, and on logout, so it doesn't come back on later visits.

## C. Order page

8. `OrderSuccessPage.vue:71`:
   - Save the delivery date with the order when it is placed, instead of computing it again from today each time the page opens.
   - For pickup orders, show "جاهز للاستلام من الجراج".
   - Fix the doubled wording: show "التوصيل المتوقع: الثلاثاء 10 نوفمبر", not "التوصيل المتوقع: يوصلك …".

## D. Cleanup (optional, low priority)

9. Remove the dead `.service-item` styles in BookingPage (lines 539-575) and fix the outdated "aria-pressed" comment.
10. These pages still import from `@/data` directly instead of going through `api/`: CategoryPage, HomePage, ProductPage, CartPage, AccountPage, CategoryNav, MobileDrawer, SearchBar. Move them to `api/` so a real backend can replace the mock later.
11. Make the hero arrow buttons at least 44px so they are easier to tap.

## Definition of done

- Register a new phone number. It shows an empty account (no bikes, orders or history), an empty checkout address and "حسابي" in the header.
- The demo phone number still shows the demo data.
- The console stays empty on every route.
