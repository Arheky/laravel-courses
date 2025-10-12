import { router } from '@inertiajs/vue3'
import axios from 'axios'

/* ------------------------------------
 * Toast
------------------------------------ */
function showToast(message, type = 'success') {
  window.dispatchEvent(new CustomEvent('show-toast', { detail: { message, type } }))
}

/* ------------------------------------
 * Loader kontrolü
------------------------------------ */
function startLoading() {
  window.dispatchEvent(new CustomEvent('loading:start'))
}
function stopLoading() {
  window.dispatchEvent(new CustomEvent('loading:end'))
}

/* ------------------------------------
 * Hata yönetimi
------------------------------------ */
function handleError(err) {
  console.error('Inertia Error:', err)
  const s = err?.response?.status

  if (s === 422)
    showToast('⚠️ Form doğrulama hatası. Lütfen bilgileri kontrol et.', 'error')
  else if (s === 403)
    showToast('⛔ Bu işlemi yapmaya yetkiniz yok.', 'error')
  else if (s === 419)
    showToast('🔒 Oturum süresi doldu. Lütfen tekrar giriş yapın.', 'error')
  else if (s >= 500)
    showToast('❌ Sunucu hatası meydana geldi.', 'error')
  else
    showToast('❌ Beklenmeyen bir hata oluştu.', 'error')
}

/* ------------------------------------
 * Güvenli wrapper (try/catch + loader)
------------------------------------ */
async function safeInertiaAction(actionFn) {
  startLoading()
  try {
    const res = await actionFn()
    stopLoading()
    return res
  } catch (err) {
    stopLoading()
    handleError(err)
    throw err
  }
}

/* ------------------------------------
 * Inertia GET
------------------------------------ */
export async function inertiaGet(url, params = {}, options = {}) {
  return safeInertiaAction(async () => {
    return router.visit(url, {
      method: 'get',
      data: params,
      ...options,
    })
  })
}

/* ------------------------------------
 * Inertia POST
------------------------------------ */
export async function inertiaPost(url, data = {}, options = {}) {
  return safeInertiaAction(async () => {
    return router.visit(url, {
      method: 'post',
      data,
      ...options,
    })
  })
}

/* ------------------------------------
 * Inertia PUT
------------------------------------ */
export async function inertiaPut(url, data = {}, options = {}) {
  return safeInertiaAction(async () => {
    return router.visit(url, {
      method: 'put',
      data,
      ...options,
    })
  })
}

/* ------------------------------------
 * Inertia DELETE
------------------------------------ */
export async function inertiaDelete(url, options = {}) {
  return safeInertiaAction(async () => {
    return router.visit(url, {
      method: 'delete',
      ...options,
    })
  })
}

/* ------------------------------------
 * Form POST
------------------------------------ */
export async function inertiaFormPost(form, url, options = {}) {
  startLoading()
  try {
    await form.post(url, {
      preserveScroll: true,
      ...options,
    })
    stopLoading()
  } catch (err) {
    stopLoading()
    handleError(err)
  }
}

/* ------------------------------------
 * Form PUT
------------------------------------ */
export async function inertiaFormPut(form, url, options = {}) {
  startLoading()
  try {
    await form.put(url, {
      preserveScroll: true,
      ...options,
    })
    stopLoading()
  } catch (err) {
    stopLoading()
    handleError(err)
  }
}

/* ------------------------------------
 * Login işlemi
------------------------------------ */
export async function inertiaLogin(email, password, remember = false) {
  return safeInertiaAction(async () => {
    try {
      const response = await axios.post(
        '/login',
        { email, password, remember },
        {
          withCredentials: true,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }
      )

      showToast('✅ Başarıyla giriş yapıldı!')
      return response
    } catch (err) {
      const status = err.response?.status
      if (status === 422) {
        const errors = err.response?.data?.errors
        if (errors) Object.values(errors).forEach((m) => showToast(m, 'error'))
        else showToast('Girdiğiniz e-posta adresi veya şifre hatalı ❌', 'error')
      } else if (status === 419) {
        showToast('Oturum süresi doldu, lütfen sayfayı yenileyin.', 'warning')
      } else {
        handleError(err)
      }
      return null
    }
  })
}

/* ------------------------------------
 * Register işlemi
------------------------------------ */
export async function inertiaRegister(name, email, password, password_confirmation) {
  return safeInertiaAction(async () => {
    try {
      const response = await axios.post(
        '/register',
        { name, email, password, password_confirmation },
        {
          withCredentials: true,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }
      )
      showToast('🎉 Kayıt başarılı! Hoş geldiniz 👋')
      return response
    } catch (err) {
      if (err.response?.status === 422 && err.response.data?.errors) {
        const errors = err.response.data.errors
        Object.values(errors).forEach((msg) => showToast(msg, 'error'))
      } else {
        handleError(err)
      }
      throw err
    }
  })
}
