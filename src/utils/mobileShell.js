// Which mobile "app shell" pieces each route shows.

// Detail and focused flows hide the bottom tab bar; they have their own
// bottom action bar (buy, book a viewing, place order) instead.
const NO_TABBAR = new Set(['product', 'bike-detail', 'checkout', 'order-success'])

// Top-level screens: no back button in the mobile header.
const ROOTS = new Set(['home', 'booking', 'cart', 'account', 'login', 'category', 'bikes', 'search'])

// Browsing screens keep the search field open in the mobile header.
const SEARCH_OPEN = new Set(['home', 'category', 'search', 'bikes'])

export const showsTabBar = (route) => !NO_TABBAR.has(route.name)
export const isRootScreen = (route) => ROOTS.has(route.name)
export const keepsSearchOpen = (route) => SEARCH_OPEN.has(route.name)
