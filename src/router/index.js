import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/pages/HomePage.vue')
  },
  {
    path: '/c/:category',
    name: 'category',
    component: () => import('@/pages/CategoryPage.vue')
  },
  {
    path: '/search',
    name: 'search',
    component: () => import('@/pages/SearchPage.vue')
  },
  {
    path: '/p/:slug',
    name: 'product',
    component: () => import('@/pages/ProductPage.vue')
  },
  {
    path: '/cart',
    name: 'cart',
    component: () => import('@/pages/CartPage.vue')
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: () => import('@/pages/CheckoutPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/order/:id',
    name: 'order-success',
    component: () => import('@/pages/OrderSuccessPage.vue')
  },
  {
    path: '/bikes',
    name: 'bikes',
    component: () => import('@/pages/BikesListingPage.vue')
  },
  {
    path: '/bikes/:slug',
    name: 'bike-detail',
    component: () => import('@/pages/BikeDetailPage.vue')
  },
  {
    path: '/booking',
    name: 'booking',
    component: () => import('@/pages/BookingPage.vue')
  },
  {
    path: '/account',
    name: 'account',
    component: () => import('@/pages/AccountPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/LoginPage.vue')
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth) {
    const isAuthed = localStorage.getItem('ayg_auth') === 'true'
    if (!isAuthed) {
      return next({ path: '/login', query: { redirect: to.fullPath } })
    }
  }
  next()
})

export default router
