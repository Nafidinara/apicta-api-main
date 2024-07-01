import { showToast } from '@/plugins/toast'
import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'
import routes from '~pages'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  // https://stackoverflow.com/questions/74014240/laravel-vite-production-build-redirecting-to-build-path-in-url
  history: createWebHistory(),
  routes: [
    ...setupLayouts(routes),
  ],
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta?.requiresAuth && !authStore.isAuthenticated) {
    // Arahkan ke rute login jika tidak terautentikasi 
    showToast("You are not logged in yet", "error")
    next("/login")
  } else if (to.meta?.allowedRoles && !to.meta.allowedRoles?.includes(authStore.role)) {
    // Arahkan ke rute login jika role tidak sesuai
    showToast("You don't have access to the page", "error")
    next("/login")
  } else {
    next() // Lanjutkan navigasi 
  }
})

// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
export default router
