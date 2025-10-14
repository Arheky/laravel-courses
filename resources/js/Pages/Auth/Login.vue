<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, onMounted, watch, nextTick } from 'vue'
import lottie from 'lottie-web'
import hourglassAnim from '@/Animations/hourglass.json'

/* Kilit durumu */
const isLocked = ref(false)
const lockSeconds = ref(0)
let timer = null
let toastShown = false
let lottieInstance = null

const hourglassRef = ref(null)
const page = usePage()
const canResetPassword = page.props.canResetPassword ?? true

/* Şifre görünürlüğü */
const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

/* Lottie kontrolü */
watch(isLocked, async (locked) => {
  await nextTick()
  if (locked && hourglassRef.value) {
    if (lottieInstance) lottieInstance.destroy()
    lottieInstance = lottie.loadAnimation({
      container: hourglassRef.value,
      renderer: 'svg',
      loop: true,
      autoplay: true,
      animationData: hourglassAnim,
    })
  } else if (lottieInstance) {
    lottieInstance.destroy()
    lottieInstance = null
  }
})

/* Kilidi başlat */
function startLock(seconds) {
  clearInterval(timer)
  isLocked.value = true
  lockSeconds.value = seconds
  localStorage.setItem('rateLimiter', JSON.stringify({ until: Date.now() + seconds * 1000 }))

  if (!toastShown) {
    toastShown = true
    window.showToast('Çok fazla hatalı giriş denemesi! Lütfen bekleyin ⏳', 'warning')
  }

  timer = setInterval(() => {
    lockSeconds.value--
    if (lockSeconds.value <= 0) {
      clearInterval(timer)
      isLocked.value = false
      toastShown = false
      localStorage.removeItem('rateLimiter')
      window.showToast('Tekrar giriş yapabilirsiniz ✅', 'info')
    } else {
      localStorage.setItem('rateLimiter', JSON.stringify({ until: Date.now() + lockSeconds.value * 1000 }))
    }
  }, 1000)
}

/* Sayfa yenilense bile kalan süreyi koru */
onMounted(() => {
  const stored = localStorage.getItem('rateLimiter')
  if (stored) {
    const data = JSON.parse(stored)
    const now = Date.now()
    if (data.until > now) {
      const remaining = Math.ceil((data.until - now) / 1000)
      startLock(remaining)
    } else {
      localStorage.removeItem('rateLimiter')
    }
  }
})

/* Giriş işlemi */
function submit() {
  if (isLocked.value)
    return window.showToast(`Lütfen ${lockSeconds.value} saniye bekleyin ⏳`, 'warning')

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/
  if (!form.email && !form.password)
    return window.showToast('E-posta ve şifre boş bırakılamaz 🚫', 'warning')
  if (!form.email)
    return window.showToast('E-posta adresinizi girin 📧', 'warning')
  if (!emailRegex.test(form.email))
    return window.showToast('Geçerli bir e-posta adresi girin (örnek@site.com) 📮', 'warning')
  if (!form.password)
    return window.showToast('Şifrenizi girin 🔑', 'warning')

  let loadingToastId = null

  form.post(route('login.store'), {
    preserveScroll: true,
    preserveState: false,
    onStart: () => {
      loadingToastId = window.$toast.info('Bilgiler doğrulanıyor...', {
        position: 'top-center',
        autoClose: false,
        closeOnClick: false,
      })
    },
    onSuccess: () => {
     if (loadingToastId) window.$toast.remove(loadingToastId)
       const props = usePage().props
       const hasErrors = props?.errors && Object.keys(props.errors).length > 0
       const isLoggedIn = !!props?.auth?.user
       const stillOnLogin = route().current('login')
     if (hasErrors || !isLoggedIn || stillOnLogin) {
       window.showToast('Girdiğiniz e-posta adresi veya şifre hatalı ❌', 'error')
       return
     }

     window.showToast('Hoş geldin 👋 Başarıyla giriş yaptın!', 'success')
  },
    onError: (errors) => {
      if (loadingToastId) window.$toast.remove(loadingToastId)
      const msg = Object.values(errors).join(' ')
      const match = msg.match(/(\d+)\s*saniye/)
      if (match) return startLock(parseInt(match[1]))

      if (errors.email || errors.password)
        Object.values(errors).forEach((m) => window.showToast(m, 'error'))
      else
        window.showToast('Girdiğiniz e-posta adresi veya şifre hatalı ❌', 'error')
    },
    onFinish: () => {
      if (loadingToastId) window.$toast.remove(loadingToastId)
      form.reset('password')
    },
  })
}
</script>

<template>
  <GuestLayout>
    <div
      class="relative min-h-screen flex flex-col items-center justify-center px-6 
             bg-transparent text-gray-800 dark:text-gray-100 transition-colors duration-300 z-10"
    >
      <!-- Başlık -->
      <section
        v-motion
        :initial="{ opacity: 0, y: 40 }"
        :enter="{ opacity: 1, y: 0, transition: { duration: 600 } }"
        class="text-center mb-10 mt-12"
      >
        <h1
          class="text-5xl font-extrabold leading-[1.25] tracking-tight bg-clip-text text-transparent 
                 bg-gradient-to-r from-indigo-500 via-blue-500 to-purple-500 
                 dark:from-indigo-300 dark:to-purple-300"
        >
          🔐 Giriş Yap
        </h1>
        <p class="mt-3 text-gray-600 dark:text-gray-300 text-lg max-w-xl mx-auto">
          Hesabına erişmek için bilgilerini gir ve öğrenmeye devam et 🚀
        </p>
      </section>

      <!-- Giriş Kartı -->
      <div
        class="w-full max-w-md bg-white/80 dark:bg-white/10 backdrop-blur-lg border border-indigo-400/30 dark:border-indigo-600/30 
               rounded-2xl p-8 shadow-md hover:shadow-xl transition transform hover:scale-[1.02]"
      >
        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium mb-1">E-posta</label>
            <input
              v-model="form.email"
              type="email"
              class="input"
              placeholder="ornek@mail.com"
              :disabled="isLocked"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Şifre</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="input pr-10"
                placeholder="••••••••"
                :disabled="isLocked"
              />
              <button
                type="button"
                class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                @click="showPassword = !showPassword"
              >
                {{ showPassword ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                v-model="form.remember"
                class="rounded border-gray-300 dark:border-gray-700"
                :disabled="isLocked"
              />
              Beni hatırla
            </label>
            <Link
              v-if="canResetPassword"
              :href="route('password.request')"
              class="text-sm text-blue-600 hover:underline"
            >
              Şifremi unuttum?
            </Link>
          </div>

          <button
            type="submit"
            class="btn-primary w-full flex items-center justify-center gap-2 py-3"
            :disabled="form.processing || isLocked"
          >
            <!-- Lottie kum saati -->
            <div v-if="isLocked" ref="hourglassRef" class="w-10 h-10"></div>

            <!-- Yükleme animasyonu -->
            <svg
              v-else-if="form.processing"
              class="animate-spin h-6 w-6 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              />
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
              />
            </svg>

            <span class="text-base font-medium">
              {{
                isLocked
                  ? `Bekle (${lockSeconds})`
                  : form.processing
                  ? 'Giriş Yapılıyor...'
                  : '🚀 Giriş Yap'
              }}
            </span>
          </button>

          <!-- Alt bağlantılar -->
          <div
            class="text-center mt-6 text-sm text-gray-600 dark:text-gray-400 space-y-3"
          >
            <p>
              Hesabın yok mu?
              <Link
                :href="route('register')"
                class="text-indigo-600 hover:underline font-semibold"
              >
                ✨ Kayıt Ol
              </Link>
            </p>

            <Link
              :href="route('home')"
              class="inline-flex items-center justify-center gap-2 text-white bg-gradient-to-r from-blue-500 to-purple-500
                     hover:from-blue-600 hover:to-purple-600 px-4 py-2 rounded-lg shadow-md transition-all
                     transform hover:scale-[1.05] hover:shadow-lg text-sm font-medium"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15 19l-7-7 7-7"
                />
              </svg>
              Ana Sayfaya Dön
            </Link>
          </div>
        </form>
      </div>
    </div>
  </GuestLayout>
</template>

<style scoped>
.input {
  @apply w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
         text-gray-900 dark:text-gray-100 px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 transition
         disabled:opacity-60 disabled:cursor-not-allowed;
}
.btn-primary {
  @apply px-5 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700
         text-white font-semibold shadow-md transition transform hover:scale-[1.03]
         disabled:opacity-60 disabled:cursor-not-allowed;
}
</style>
