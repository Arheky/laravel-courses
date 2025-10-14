import _ from 'lodash'
window._ = _

import axios from 'axios'
window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const appUrlMeta = typeof document !== 'undefined'
  ? document.querySelector('meta[name="app-url"]')
  : null

const appUrl = (appUrlMeta && appUrlMeta.content)
  || (typeof window !== 'undefined' ? window.location.origin : '/')

window.axios.defaults.baseURL = appUrl || '/'
