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

// Rate limit interceptor (429 durumunu yakala)
window.axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 429) {
      const msg = error.response.data.errors?.email?.[0] || 'Çok fazla hatalı giriş denemesi!'
      const match = msg.match(/(\d+)\s*saniye/)
      if (match) {
        // Global rate limit başlat
        window.appState.rateRemaining = parseInt(match[1])
        window.appState.rateLimited = true

        // Önceden varsa temizle
        if (window.appState.rateTimer) clearInterval(window.appState.rateTimer)
        toast.dismiss()

        const toastId = toast.warning(
          `Çok fazla hatalı giriş denemesi! ${window.appState.rateRemaining} saniye bekleyin ⏳`,
          { position: 'top-center', autoClose: false }
        )

        // Geri sayım başlat
        window.appState.rateTimer = setInterval(() => {
          window.appState.rateRemaining--
          if (window.appState.rateRemaining > 0) {
            toast.update(toastId, {
              render: `Çok fazla hatalı giriş denemesi! ${window.appState.rateRemaining} saniye bekleyin ⏳`,
            })
          } else {
            clearInterval(window.appState.rateTimer)
            window.appState.rateLimited = false
            toast.dismiss(toastId)
            toast.info('Tekrar giriş yapabilirsiniz ✅')
          }
        }, 1000)
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

// Gereksiz 404 hatalarını bastır (örneğin Vite asset hataları)
window.addEventListener('error', (e) => {
  if (
    e.target.tagName === 'SCRIPT' ||
    e.target.tagName === 'LINK' ||
    e.target.tagName === 'IMG'
  ) {
    e.preventDefault()
  }
}, true)
