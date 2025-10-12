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
          ✨ Kayıt Ol
        </h1>
        <p class="mt-3 text-gray-600 dark:text-gray-300 text-lg max-w-xl mx-auto">
          Yeni bir hesap oluştur ve öğrenmeye hemen başla 🚀
        </p>
      </section>

      <!-- Kayıt Kartı -->
      <div
        class="w-full max-w-md bg-white/80 dark:bg-white/10 backdrop-blur-lg border border-indigo-400/30 dark:border-indigo-600/30 
               rounded-2xl p-8 shadow-md hover:shadow-xl transition transform hover:scale-[1.02]"
      >
        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium mb-1">Ad Soyad</label>
            <input v-model="form.name" type="text" autocomplete="name" class="input" placeholder="Adınızı girin" :disabled="isLocked" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">E-posta</label>
            <input v-model="form.email" type="email" autocomplete="username" class="input" placeholder="ornek@mail.com" :disabled="isLocked" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Şifre</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
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
            <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">
              Şifre en az 8 karakter olmalı ve sadece harf, rakam, _ ve * içerebilir.
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Şifre Tekrar</label>
            <div class="relative">
              <input
                v-model="form.password_confirmation"
                :type="showConfirm ? 'text' : 'password'"
                autocomplete="new-password"
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

          <!-- Kayıt Butonu -->
          <button
            type="submit"
            class="btn-primary w-full flex items-center justify-center gap-2"
            :disabled="form.processing || isLocked"
          >
            <div v-if="isLocked" ref="lottieContainer" class="lottie-hourglass"></div>

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
                  ? 'Kayıt Yapılıyor...'
                  : '✨ Kayıt Ol'
              }}
            </span>
          </button>
        </form>

        <!-- Alt bağlantılar -->
        <div class="text-center text-sm mt-6 text-gray-600 dark:text-gray-400 space-y-3">
          <p>
            Zaten hesabın var mı?
            <Link :href="route('login')" class="text-indigo-600 hover:underline font-semibold">
              🔐 Giriş Yap
            </Link>
          </p>

          <!-- Ana sayfa butonu -->
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
      </div>

      <!-- Divider -->
      <div class="h-[2px] w-40 bg-gradient-to-r from-indigo-500 via-blue-400 to-purple-500 rounded-full opacity-60 mt-12"></div>

      <footer class="text-xs mt-8 text-gray-500 dark:text-gray-400">
        © 2025 LaravelCourses — Laravel 12 • Vue 3 • Inertia
      </footer>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import lottie from 'lottie-web'
import hourglassAnim from '@/Animations/hourglass.json'

/* Rate limit ve form durumu */
const isLocked = ref(false)
const lockSeconds = ref(0)
let timer = null
let toastShown = false
const lottieContainer = ref(null)
const showPassword = ref(false)
const showConfirm = ref(false)

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

/* CSRF cookie al */
onMounted(async () => {
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    console.info('[Register] CSRF cookie hazır ✅')

    const stored = localStorage.getItem('registerRateLimit')
    if (stored) {
      const data = JSON.parse(stored)
      const now = Date.now()
      if (data.until > now) {
        const remaining = Math.ceil((data.until - now) / 1000)
        startLock(remaining)
      } else {
        localStorage.removeItem('registerRateLimit')
      }
    }
  } catch {
    window.showToast('Güvenlik doğrulaması başarısız. Sayfayı yenileyin.', 'error')
  }
})

/* Rate limiter kilidi başlat */
function startLock(seconds) {
  clearInterval(timer)
  isLocked.value = true
  lockSeconds.value = seconds
  localStorage.setItem('registerRateLimit', JSON.stringify({ until: Date.now() + seconds * 1000 }))

  if (!toastShown) {
    toastShown = true
    window.showToast('Çok fazla kayıt denemesi! Lütfen bekleyin ⏳', 'warning')
  }

  if (lottieContainer.value) {
    lottie.loadAnimation({
      container: lottieContainer.value,
      renderer: 'svg',
      loop: true,
      autoplay: true,
      animationData: hourglassAnim,
    })
  }

  timer = setInterval(() => {
    lockSeconds.value--
    if (lockSeconds.value <= 0) {
      clearInterval(timer)
      isLocked.value = false
      toastShown = false
      localStorage.removeItem('registerRateLimit')
      window.showToast('Tekrar kayıt olabilirsiniz ✅', 'info')
    } else {
      localStorage.setItem('registerRateLimit', JSON.stringify({ until: Date.now() + lockSeconds.value * 1000 }))
    }
  }, 1000)
}

/* Form gönderimi */
function submit() {
  if (isLocked.value)
    return window.showToast(`Lütfen ${lockSeconds.value} saniye bekleyin ⏳`, 'warning')

  // Kurallar
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const nameRegex = /^[a-zA-ZığüşöçİĞÜŞÖÇ0-9\s]+$/; // sadece harf, rakam ve boşluk
  const passwordRegex = /^[a-zA-ZığüşöçİĞÜŞÖÇ0-9_*]+$/; // ✅ sadece harf, rakam, _ ve *

  // Alan kontrolü
  if (!form.name || !form.email || !form.password)
    return window.showToast('Tüm alanları doldurmalısın 🚫', 'warning');

  if (!nameRegex.test(form.name))
    return window.showToast('Ad soyad özel karakter içeremez ⚠️', 'warning');

  if (!emailRegex.test(form.email))
    return window.showToast('Geçerli bir e-posta adresi girin (örnek@site.com) 📮', 'warning');

  if (form.password.length < 8)
    return window.showToast('Şifre en az 8 karakter olmalıdır 🔐', 'warning');

  if (!passwordRegex.test(form.password))
    return window.showToast('Şifre sadece harf, rakam, _ ve * karakterlerinden oluşabilir ❌', 'warning');

  if (form.password !== form.password_confirmation)
    return window.showToast('Şifreler eşleşmiyor ❌', 'warning');

  let loadingToastId = null;

  form.post(route('register'), {
    preserveScroll: true,
    preserveState: false,
    onStart: () => {
      loadingToastId = window.$toast.info('Hesabın oluşturuluyor...', {
        position: 'top-center',
        autoClose: false,
        closeOnClick: false,
      });
    },
    onSuccess: () => {
      if (loadingToastId) window.$toast.remove(loadingToastId);
      window.showToast('🎉 Kayıt başarılı! Şimdi giriş yapabilirsiniz 👋', 'success');
      setTimeout(() => (window.location.href = route('login')), 1200);
    },
    onError: (errors) => {
      if (loadingToastId) window.$toast.remove(loadingToastId);
      const msg = Object.values(errors).join(' ');
      const match = msg.match(/(\d+)\s*saniye/);
      if (match) return startLock(parseInt(match[1]));

      if (errors.email && errors.email.includes('zaten alınmış'))
        window.showToast('Bu e-posta adresi zaten kayıtlı ⚠️', 'error');
      else if (Object.keys(errors).length)
        Object.values(errors).forEach((msg) => window.showToast(msg, 'error'));
      else
        window.showToast('Kayıt başarısız oldu. Lütfen tekrar deneyin ❌', 'error');
    },
    onFinish: () => {
      if (loadingToastId) window.$toast.remove(loadingToastId);
      form.reset();
    },
  });
}
</script>

<style scoped>
.input {
  @apply w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 
         text-gray-900 dark:text-gray-100 px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 transition;
}
.btn-primary {
  @apply px-5 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 
         text-white font-semibold shadow-md transition transform hover:scale-[1.03] disabled:opacity-70 disabled:cursor-not-allowed;
}
.lottie-hourglass {
  width: 40px;
  height: 40px;
}
</style>
