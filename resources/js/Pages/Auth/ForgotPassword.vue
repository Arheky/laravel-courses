<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import lottie from 'lottie-web'
import hourglassAnim from '@/Animations/hourglass.json'

const page = usePage()
const status = page.props.status || null
const demoLink = ref(page.props.demo_reset_link || page.props.flash?.demo_reset_link || null)
const showLinkPanel = ref(!!demoLink.value)

const form = useForm({ email: '' })

/* Rate limiter */
const isLocked = ref(false)
const lockSeconds = ref(0)
let timer = null
let toastShown = false
const STORAGE_KEY = 'rateLimiter:forgot'

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

onMounted(() => {
  if (status) window.showToast(status, 'success')
  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored) {
    const data = JSON.parse(stored)
    const now = Date.now()
    if (data.until > now) startLock(Math.ceil((data.until - now) / 1000))
    else localStorage.removeItem(STORAGE_KEY)
  }

  // Link paneli 60 saniye sonra gizlensin
  if (showLinkPanel.value) {
    setTimeout(() => (showLinkPanel.value = false), 60000)
  }
})

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

/* Kopyala işlemi */
function copyLink(link) {
  navigator.clipboard.writeText(link)
  window.showToast('Bağlantı kopyalandı 📋', 'info')
}

/* Paneli yeniden görünür yap */
function showLinkAgain() {
  showLinkPanel.value = true
  setTimeout(() => (showLinkPanel.value = false), 60000)
}

/* E-posta gönderimi */
function submit() {
  if (isLocked.value)
    return window.showToast(`Lütfen ${lockSeconds.value} saniye bekleyin ⏳`, 'warning')

  if (!form.email)
    return window.showToast('E-posta adresinizi girin 📧', 'warning')

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/
  if (!emailRegex.test(form.email))
    return window.showToast('Geçerli bir e-posta adresi girin (örnek@site.com) 📮', 'warning')

  let loadingToastId = null

  form.post(route('password.email'), {
    preserveScroll: true,
    preserveState: false,
    onStart: () => {
      loadingToastId = window.$toast.info('Sıfırlama bağlantısı oluşturuluyor...', {
        position: 'top-center',
        autoClose: false,
      })
    },
    onSuccess: () => {
      if (loadingToastId) window.$toast.remove(loadingToastId)
      demoLink.value = page.props.flash?.demo_reset_link || null
      showLinkPanel.value = !!demoLink.value
      if (showLinkPanel.value) setTimeout(() => (showLinkPanel.value = false), 60000)
      window.showToast('📩 Şifre sıfırlama bağlantısı oluşturuldu!', 'success')
    },
    onError: (errors) => {
      if (loadingToastId) window.$toast.remove(loadingToastId)
      const msg = Object.values(errors).join(' ')
      const match = msg.match(/(\d+)\s*saniye/)
      if (match) return startLock(parseInt(match[1]))
      if (errors.email) window.showToast(errors.email, 'error')
      else window.showToast('E-posta gönderilemedi. Lütfen tekrar deneyin ❌', 'error')
    },
    onFinish: () => loadingToastId && window.$toast.remove(loadingToastId),
  })
}
</script>

<template>
  <GuestLayout>
    <div class="relative min-h-screen flex flex-col items-center justify-center px-6 z-10 text-gray-800 dark:text-gray-100">
      <section v-motion :initial="{ opacity: 0, y: 40 }" :enter="{ opacity: 1, y: 0 }"
        class="text-center mb-10 mt-12">
        <h1 class="text-5xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-blue-500 to-purple-500">
          🔑 Şifremi Unuttum
        </h1>
        <p class="mt-3 text-gray-600 dark:text-gray-300 text-lg max-w-xl mx-auto">
          E-posta adresini gir, sana sıfırlama bağlantısı oluşturalım 🚀
        </p>
      </section>

      <div class="w-full max-w-md bg-white/80 dark:bg-white/10 backdrop-blur-lg border border-indigo-400/30 dark:border-indigo-600/30 rounded-2xl p-8 shadow-md">
        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium mb-1">E-posta Adresi</label>
            <input v-model="form.email" type="email" class="input" placeholder="ornek@mail.com" :disabled="isLocked" />
          </div>

          <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2"
            :disabled="form.processing || isLocked">
            <div v-if="isLocked" ref="lottieRef" class="w-9 h-9 -ml-1"></div>
            <svg v-else-if="form.processing" class="animate-spin h-5 w-5 text-white"
              xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
            </svg>
            <span>{{ isLocked ? `Bekle (${lockSeconds})` : form.processing ? 'Gönderiliyor...' : '📨 Sıfırlama Bağlantısı Gönder' }}</span>
          </button>
        </form>

        <!-- Demo link paneli -->
        <transition name="fade">
          <div v-if="showLinkPanel && demoLink" class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/40 border border-indigo-300 dark:border-indigo-700 rounded-lg text-center shadow-sm">
            <p class="text-sm mb-2 text-gray-700 dark:text-gray-300">🔗 Şifre sıfırlama bağlantısı:</p>
            <a :href="demoLink" target="_blank"
               class="block text-indigo-600 dark:text-indigo-400 font-medium break-all hover:underline">
              {{ demoLink }}
            </a>
            <div class="mt-3 flex justify-center gap-3">
              <button class="text-xs text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded-md"
                      @click="copyLink(demoLink)">📋 Kopyala</button>
              <button class="text-xs text-indigo-600 border border-indigo-600 px-3 py-1 rounded-md hover:bg-indigo-100 dark:hover:bg-indigo-800"
                      :disabled="isLocked" @click="showLinkAgain">♻️ Yeniden Göster</button>
            </div>
          </div>
        </transition>

        <p class="text-center text-sm mt-6 text-gray-600 dark:text-gray-400">
          Parolanı hatırladın mı?
          <Link :href="route('login')" class="text-indigo-600 hover:underline font-semibold">🔐 Giriş Yap</Link>
        </p>
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
.fade-enter-active, .fade-leave-active { transition: opacity 0.6s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
