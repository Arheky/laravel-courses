import axios from 'axios'

window.axios = axios
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

axios.defaults.baseURL = ''

axios.get('/sanctum/csrf-cookie').catch(() => {
  console.warn('⚠️ CSRF cookie alınamadı.')
})

export default axios
