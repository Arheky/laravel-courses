<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, useForm, usePage, router } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import lottie from 'lottie-web'
import hourglassAnim from '@/Animations/hourglass.json'

/* Form verileri */
const page = usePage()
const status = page.props.status || null

const form = useForm({
  token: page.props.token,
  email: page.props.email || '',
  password: '',
  password_confirmation: '',
})

/* Rate limit */
const isLocked = ref(false)
const lockSeconds = ref(0)
let timer = null
let toastShown = false
const STORAGE_KEY = 'rateLimiter:reset'

function startLock(seconds) {
  clearInterval(timer)
  isLocked.value = true
  lockSeconds.value = seconds
  localStorage.setItem(STORAGE_KEY, JSON.stringify({ until: Date.now() + seconds * 1000 }))

  if (!toastShown) {
    toastShown = true
    window.showToast('Çok fazla deneme yaptınız! Lütfen bekleyin ⏳', 'warning')
  }

  timer = setInterval(() => {
    lockSeconds.value--
    if (lockSeconds.value <= 0) {
      clearInterval(timer)
      isLocked.value = false
      toastShown = false
      localStorage.removeItem(STORAGE_KEY)
      window.showToast('Tekrar deneyebilirsiniz ✅', 'info')
    } else {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ until: Date.now() + lockSeconds.value * 1000 }))
    }
  }, 1000)
}

/* Sayfa yenilense bile kalan süreyi koru */
onMounted(() => {
  if (status) window.showToast(status, 'success')

  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored) {
    const data = JSON.parse(stored)
    const now = Date.now()
    if (data.until > now) {
      const remaining = Math.ceil((data.until - now) / 1000)
      startLock(remaining)
    } else {
      localStorage.removeItem(STORAGE_KEY)
    }
  }
})

/* Şifre görünürlüğü */
const showPassword = ref(false)
const showConfirm = ref(false)

/* Lottie kum saati */
const lottieRef = ref(null)
let lottieInstance = null
watch(isLocked, (locked) => {
  if (locked && lottieRef.value) {
    requestAnimationFrame(() => {
      if (lottieInstance) lottieInstance.destroy()
      lottieInstance = lottie.loadAnimation({
        container: lottieRef.value,
        renderer: 'svg',
        loop: true,
        autoplay: true,
        animationData: hourglassAnim,
      })
    })
  } else if (lottieInstance) {
    lottieInstance.destroy()
    lottieInstance = null
  }
})

/* Şifre sıfırlama gönder */
function submit() {
  if (isLocked.value)
    return window.showToast(`Lütfen ${lockSeconds.value} saniye bekleyin ⏳`, 'warning')

  if (!form.password || !form.password_confirmation)
    return window.showToast('Tüm alanları doldurmalısınız 🚫', 'warning')

  // Minimum 8 karakter kontrolü
  if (form.password.length < 8)
    return window.showToast('Şifre en az 8 karakter olmalıdır 🔐', 'warning')

  if (form.password !== form.password_confirmation)
    return window.showToast('Şifreler eşleşmiyor ❌', 'warning')

  let loadingToastId = null

  form.post(route('password.store'), {
    preserveScroll: true,
    preserveState: false,

    onStart: () => {
      loadingToastId = window.$toast.info('Şifre sıfırlanıyor...', {
        position: 'top-center',
        autoClose: false,
        closeOnClick: false,
      })
    },

    onSuccess: () => {
      if (loadingToastId) window.$toast.remove(loadingToastId)
      window.showToast('Şifre başarıyla güncellendi ✅', 'success')

      // Otomatik "Hoş geldin" toast için sessionStorage mesajı kaydet
      sessionStorage.setItem('passwordResetSuccess', 'true')

      // Ana sayfaya yönlendir
      setTimeout(() => router.visit(route('home')), 1500)
    },

    onError: (errors) => {
      if (loadingToastId) window.$toast.remove(loadingToastId)
      const msg = Object.values(errors).join(' ')
      const match = msg.match(/(\d+)\s*saniye/)
      if (match) return startLock(parseInt(match[1]))

      if (errors.email || errors.password)
        Object.values(errors).forEach((m) => window.showToast(m, 'error'))
      else
        window.showToast('İşlem başarısız oldu. Lütfen tekrar deneyin ❌', 'error')
    },

    onFinish: () => {
      if (loadingToastId) window.$toast.remove(loadingToastId)
      form.reset('password', 'password_confirmation')
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
          🔐 Şifre Yenile
        </h1>
        <p class="mt-3 text-gray-600 dark:text-gray-300 text-lg max-w-xl mx-auto">
          Yeni şifreni belirle ve güvenli bir şekilde giriş yap 🚀
        </p>
      </section>

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
              class="input bg-gray-100 dark:bg-gray-800 cursor-not-allowed"
              readonly
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Yeni Şifre</label>
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

          <div>
            <label class="block text-sm font-medium mb-1">Şifre Tekrar</label>
            <div class="relative">
              <input
                v-model="form.password_confirmation"
                :type="showConfirm ? 'text' : 'password'"
                class="input pr-10"
                placeholder="••••••••"
                :disabled="isLocked"
              />
              <button
                type="button"
                class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                @click="showConfirm = !showConfirm"
              >
                {{ showConfirm ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <button
            type="submit"
            class="btn-primary w-full flex items-center justify-center gap-2"
            :disabled="form.processing || isLocked"
          >
            <div v-if="isLocked" ref="lottieRef" class="w-9 h-9 -ml-1"></div>
            <svg
              v-else-if="form.processing"
              class="animate-spin h-5 w-5 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
            </svg>

            <span>
              {{
                isLocked
                  ? `Bekle (${lockSeconds})`
                  : form.processing
                  ? 'Şifre Güncelleniyor...'
                  : '🚀 Şifreyi Güncelle'
              }}
            </span>
          </button>

          <!-- Alt bağlantılar -->
          <div class="text-center text-sm mt-6 text-gray-600 dark:text-gray-400 space-y-3">
            <p>
              Giriş sayfasına dön?
              <Link :href="route('login')" class="text-indigo-600 hover:underline font-semibold">
                🔐 Giriş Yap
              </Link>
            </p>

            <Link
              :href="route('home')"
              class="inline-flex items-center justify-center gap-2 text-white bg-gradient-to-r from-blue-500 to-purple-500
                     hover:from-blue-600 hover:to-purple-600 px-4 py-2 rounded-lg shadow-md transition-all
                     transform hover:scale-[1.05] hover:shadow-lg text-sm font-medium"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
              </svg>
              Ana Sayfaya Dön
            </Link>
          </div>
        </form>
      </div>

      <div class="h-[2px] w-40 bg-gradient-to-r from-indigo-500 via-blue-400 to-purple-500 rounded-full opacity-60 mt-12"></div>
      <footer class="text-xs mt-8 text-gray-500 dark:text-gray-400">
        © 2025 LaravelCourses — Laravel 12 • Vue 3 • Inertia
      </footer>
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
