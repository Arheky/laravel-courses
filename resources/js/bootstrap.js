import axios from 'axios'

window.axios = axios

window.axios.defaults.withCredentials = true
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

window.axios.defaults.baseURL = import.meta.env.PROD ? '' : 'http://localhost:8000'

window.axios.get('/sanctum/csrf-cookie').catch(() => console.warn('⚠️ CSRF cookie alınamadı.'))

export default window.axios
