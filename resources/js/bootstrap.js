import axios from 'axios'
window.axios = axios

// Base URL — hem Docker local, hem Render production için
window.axios.defaults.baseURL = import.meta.env.VITE_APP_URL || 'http://localhost'

// Cookie ve CSRF işlemleri için ayarlar
window.axios.defaults.withCredentials = true
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Sayfa ilk açıldığında CSRF cookie’yi garanti et
axios
  .get('/sanctum/csrf-cookie')
  .then(() => console.info('[Bootstrap] CSRF cookie hazır ✅'))
  .catch((e) => console.warn('[Bootstrap] CSRF cookie alınamadı:', e.message))
