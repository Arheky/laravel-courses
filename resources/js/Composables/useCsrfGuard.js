import axios from 'axios'
import { router } from '@inertiajs/vue3'

let initialized = false
let refreshing = false
let timer = null

export async function useCsrfGuard(intervalMinutes = 10) {
  if (initialized) return
  initialized = true

  axios.defaults.withCredentials = true
  axios.defaults.xsrfCookieName = 'XSRF-TOKEN'
  axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN'

  async function refreshCsrf() {
    if (refreshing) return
    refreshing = true
    try {
      await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
      // Çerez yazımını tamamlamak için küçük bir bekleme
      await new Promise((r) => setTimeout(r, 300))
      console.info('[CSRF] token yenilendi')
    } catch (err) {
      console.warn('[CSRF] yenileme başarısız:', err.message)
    } finally {
      refreshing = false
    }
  }

  // İlk CSRF cookie'yi al (await ile bloklayıcı şekilde)
  await refreshCsrf()

  // Periyodik yenileme
  timer = setInterval(refreshCsrf, intervalMinutes * 60 * 1000)

  // Mutasyon öncesi cookie kontrolü
  router.on('before', async (visit) => {
    const method = visit.method?.toLowerCase?.()
    const mutating = ['post', 'put', 'patch', 'delete'].includes(method)
    if (!mutating) return

    if (!document.cookie.includes('XSRF-TOKEN')) {
      console.warn('[CSRF] cookie eksik, yenileniyor...')
      await refreshCsrf()
    }

    visit.headers = {
      ...(visit.headers || {}),
      'X-Requested-With': 'XMLHttpRequest',
    }
  })

  // Axios 419 interceptor
  axios.interceptors.response.use(
    (res) => res,
    async (error) => {
      if (error.response?.status === 419 && !error.config.__retried) {
        console.warn('[CSRF] 419 tespit edildi, token yenileniyor...')
        await refreshCsrf()
        error.config.__retried = true
        return axios(error.config)
      }
      return Promise.reject(error)
    }
  )
}
