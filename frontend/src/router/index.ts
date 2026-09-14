import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/RegisterView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/',
    name: 'dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/pos',
    name: 'pos',
    component: () => import('@/views/PosView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/pembelian',
    name: 'pembelian',
    component: () => import('@/views/PembelianView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/supplier',
    name: 'supplier',
    component: () => import('@/views/PembelianView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/barang',
    name: 'barang',
    component: () => import('@/views/BarangView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/user',
    name: 'user',
    component: () => import('@/views/UserView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/pelanggan',
    name: 'pelanggan',
    component: () => import('@/views/PosView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/penjualan',
    name: 'penjualan',
    component: () => import('@/views/PenjualanView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  } else if (to.meta.guestOnly && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
