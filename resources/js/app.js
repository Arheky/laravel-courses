import './bootstrap'
import '../css/app.css'
import { createApp, h, reactive } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from 'ziggy-js'
import AppLayout from './Layouts/AppLayout.vue'

// Animasyonlar
import { MotionPlugin } from '@vueuse/motion'

// Toastify
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

// Yardımcılar
import { useSafeConsole } from './Composables/useSafeConsole'
import { useCsrfGuard } from './Composables/useCsrfGuard'

// Konsol güvenliği
useSafeConsole()

// CSRF guard
await useCsrfGuard(10)

// Axios genel ayarlar
window.axios.defaults.withCredentials = true

// Global app durumu (rate limiter dahil)
window.appState = reactive({
  rateLimited: false,
  rateRemaining: 0,
  rateTimer: null,
})

// Global toast erişimi
window.$toast = toast
window.showToast = (message, type = 'info', options = {}) => {
  const settings = { position: 'top-center', autoClose: 3000, ...options }
  switch (type) {
    case 'success':
      toast.success(message, settings)
      break
    case 'error':
      toast.error(message, settings)
      break
    case 'warning':
      toast.warning(message, settings)
      break
    default:
      toast.info(message, settings)
  }
}

// Rate limit interceptor
window.__rateToastTs = 0
window.axios.interceptors.response.use(
  (r) => r,
  (error) => {
    const res = error?.response
    if (res?.status === 429) {
      const data = res.data || {}
      const headers = res.headers || {}
      let remaining =
        (typeof data.retry_after === 'number' && data.retry_after) ||
        (typeof data.retryAfter === 'number' && data.retryAfter) ||
        (headers['x-ratelimit-retry-after'] && parseInt(headers['x-ratelimit-retry-after'], 10)) ||
        (headers['retry-after'] && parseInt(headers['retry-after'], 10)) ||
        (() => {
          const msg = data.message || data.errors?.email?.[0] || ''
          const m = msg.match(/(\d+)\s*saniye/)
          return m ? parseInt(m[1], 10) : null
        })()
      if (Number.isFinite(remaining) && remaining > 0) {
        window.dispatchEvent(new CustomEvent('auth-rate-limit', { detail: { remaining } }))
        window.appState.rateLimited = true
        window.appState.rateRemaining = remaining
      }
      const now = Date.now()
      if (!window.__rateToastTs || now - window.__rateToastTs > 2500) {
        window.__rateToastTs = now
        toast.warning('Çok fazla hatalı giriş denemesi! Lütfen bekleyin ⏳', {
          position: 'top-center',
          autoClose: 3000,
          closeOnClick: true,
          pauseOnHover: true,
        })
      }
    }
    return Promise.reject(error)
  }
)

const appName = import.meta.env.VITE_APP_NAME || 'Laravel Courses'

createInertiaApp({
  title: (title) => `${title} — ${appName}`,
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: false })
    const page = resolvePageComponent(`./Pages/${name}.vue`, pages)
    page.then((mod) => {
      if (!name.startsWith('Welcome') && !name.startsWith('Auth/')) {
        mod.default.layout = mod.default.layout || AppLayout
      }
    })
    return page
  },
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) })
    vueApp.use(plugin)
    vueApp.use(ZiggyVue)
    vueApp.use(MotionPlugin)
    vueApp.mount(el)
  },
  progress: {
    color: '#2563eb',
    includeCSS: true,
    showSpinner: true,
  },
})

if (import.meta.env.DEV) {
  window.addEventListener('error', (e) => {
    if (['SCRIPT','LINK','IMG'].includes(e.target.tagName)) e.preventDefault()
  }, true)
}
