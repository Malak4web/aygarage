# Review round 5: last fixes

Reviewed 2026-11-07 after commits 0ca33eb..4e9d439. Most of round 4 is done.

Tested in the browser and working:
- A new phone number gets an empty account and an empty checkout address.
- The demo phone (010 1234 5678) still shows the demo data.
- The checkout won't submit without an address.
- Pickup orders show "جاهز للاستلام من الجراج".
- Out-of-stock buttons are disabled.
- The console is empty on every route.

These commits introduced one regression and left a few gaps. Paths are under `src/`.

> **Update from the owner: the site is getting a Laravel + MySQL backend (see `BACKEND_BRIEF.md`).**
> - **Skip section A.** It's all about the mock localStorage layer, which the backend replaces. Those rules are now in the brief as server rules: data per user, ownership, keeping accounts after logout, and service history per bike.
> - **Do sections B and C now**, on the current frontend. Then start `BACKEND_BRIEF.md` phase 1.
> - Use section A's "Definition of done" scenario as the test at the end of backend phase 5.

## A. Data per user: SKIPPED (handled by the backend)

1. **A new user's bookings never show in Account › حجوزاتي.** I checked this in the browser: I registered a new phone and booked, the booking succeeded (#BK-3541), and حجوزاتي was empty. The cause:
   - `api/index.js:164-168` filters bookings by `b.phone`.
   - The booking payload (`BookingPage.vue:355-363`) has no `phone`, and `createBooking` doesn't add one.

   Save the user's phone on every booking, the same way orders do. Stop writing a second copy to `userStore.user.bookings` (BookingPage:368-375); the api should be the only place bookings live.
2. **The demo account sees everyone's orders and bookings.** I checked this in the browser: an order placed by a new user (#5640) showed in Karim's طلباتي. `api/index.js:141-144` and `:171-174` return everything for the demo phone. Filter by phone for every user, including the demo one. Keep the demo's own seeded orders and bookings by giving them the demo phone in the data.
3. **Service history leaks to new users.** `api.getServiceHistory()` (`api/index.js:63-66`) returns Karim's 4 visits to everyone, and AccountPage shows them (:145, :227).
   - Make it `getServiceHistory(phone)`, return demo history only for the demo phone, and filter by bike where it is shown under a bike.
   - Add an empty state to the history tab: "لسه مفيش زيارات صيانة مسجلة".
4. **A non-demo user loses their account on logout/login.** `logout` sets `user = null` (stores/user.js:150), then `login` creates a fresh empty user (:100-101), so the name and bikes are gone. Keep registered users in localStorage keyed by phone (a mock "users table" in `api/`). Login should load the saved user for that phone.

## B. Cart

5. **The stock toast is still overwritten.** When the quantity is clamped (`stores/cart.js:120-124`, :130-134), `addItem` shows the stock toast and then returns `true` (:160). The product page then shows the success toast on top of it, since the toast store has one slot. Return a status instead of a boolean:
   - `'added'`
   - `'clamped'`
   - `'out'`

   Show the success toast only for `'added'`.
6. **"اشتري دلوقتي" does nothing when the line is already at stock.** `ProductPage.vue:418-421` goes to checkout only if `addItem` returns true. Buy now should still go to `/checkout` when the item is already in the cart.
7. **The home hero "kit" action on an out-of-stock item** (`HomePage.vue:295`) silently sends the user to `/cart`. Show the out-of-stock toast and stay on the page.

## C. Small

8. **The header shows "حسابي" twice** for a user with no name, once in `<small>` and once in `<b>` (AppHeader.vue:29). For a user with no name, show "أهلاً" over "حسابي".
9. **Pickup order page:**
   - It still shows "العنوان: …", which isn't needed for pickup. Show the garage address instead.
   - The thank-you line mentions "تفاصيل الشحن وميعاد التوصيل", which doesn't fit pickup. Use "هنبعتلك رسالة على الواتساب لما الطلب يجهز".

   Checkout should also not require the street address when pickup is selected.
10. **Settings save makes the saved-address button reappear.** Saving Settings with a blank street sets `address = {}`, and the button comes back (AccountPage.vue:522). Save `null` when the street is empty.

## Definition of done

For sections B and C: the console is empty, and items 5-10 behave as described.

After backend phase 5, test in the browser:
- Register user A, book a service and place an order. Both show in A's account.
- Log out and register user B. B's account has no orders, no bookings and no service history.
- Log out and log in as A again. A's name, bikes, orders and booking are still there.
- Log in with the demo phone. You see only the demo data, not A's or B's.
- The console stays empty on every route.
