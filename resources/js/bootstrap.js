import axios from 'axios'

axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// 🌍 Ortama göre dinamik backend URL
const baseURL =
  import.meta.env.MODE === 'production'
    ? 'https://laravel-courses.onrender.com' // Render domain
    : 'http://localhost:8000' // Local geliştirme

axios.defaults.baseURL = baseURL

// 🔒 CSRF cookie otomatik ayarı
axios
  .get(`${baseURL}/sanctum/csrf-cookie`)
  .catch(() => console.warn('⚠️ CSRF cookie alınamadı.'))

export default axios
