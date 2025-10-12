<template>
  <GuestLayout>
    <div
      class="relative min-h-screen flex flex-col items-center justify-center px-6 
             bg-transparent text-gray-800 dark:text-gray-100 transition-colors duration-300 z-10"
    >
      <!-- Hero -->
      <section
        v-motion
        :initial="{ opacity: 0, y: 40 }"
        :enter="{ opacity: 1, y: 0, transition: { duration: 600 } }"
        class="text-center mb-12 mt-12"
      >
        <h1
          class="text-5xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-blue-500 to-purple-500 dark:from-indigo-300 dark:to-purple-300"
        >
          🎓 LaravelCourses
        </h1>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
          Modern, güvenli ve hızlı bir <strong>kurs yönetim platformuna</strong> hoş geldiniz.
        </p>

        <!-- Dinamik buton alanı -->
        <div class="flex flex-wrap gap-3 justify-center mt-6">
          <!-- Giriş yapılmamış kullanıcı -->
          <template v-if="!auth?.user">
            <Link
              :href="route('login')"
              class="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-md transition transform hover:scale-[1.03]"
            >
              🚀 Giriş Yap
            </Link>
            <Link
              :href="route('register')"
              class="px-6 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-700 font-semibold transition transform hover:scale-[1.03]"
            >
              ✨ Kayıt Ol
            </Link>
          </template>

          <!-- Giriş yapılmış kullanıcı -->
          <template v-else>
            <button
              @click="goToDashboard"
              class="px-6 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold shadow-md transition transform hover:scale-[1.03]"
            >
              🧭 Devam Et
            </button>

            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="px-6 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold shadow-md transition transform hover:scale-[1.03]"
            >
              🚪 Çıkış Yap
            </Link>
          </template>
        </div>
      </section>

      <!-- Divider -->
      <div class="h-[2px] w-40 bg-gradient-to-r from-indigo-500 via-blue-400 to-purple-500 rounded-full opacity-60 mb-12"></div>

      <!-- Özellikler Bölümü -->
      <section
        v-motion
        :initial="{ opacity: 0, y: 50 }"
        :enter="{ opacity: 1, y: 0, transition: { duration: 700, delay: 200 } }"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl w-full"
      >
        <!-- Admin Paneli -->
        <div
          class="bg-white/80 dark:bg-white/10 backdrop-blur-lg border border-indigo-400/30 dark:border-indigo-600/30 
                rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:scale-[1.02]"
        >
          <h3 class="text-lg font-bold mb-3 text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
            👩‍💻 Admin Paneli
          </h3>
          <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
            <li>🔐 Rol tabanlı yetkilendirme (Policy + Gate)</li>
            <li>🧩 Güvenli CRUD (Kurs, Ders, Öğrenci)</li>
            <li>🧱 Policy yapısı: Course, Lesson, Student, Dashboard</li>
            <li>🧾 Request doğrulama: Store / Update / Profile / Dashboard</li>
            <li>📊 Dashboard & İstatistik görünümü</li>
            <li>📚 Kurs & Ders Yönetimi (ilişkili veri temizleme dahil)</li>
            <li>🧠 Yetki bazlı erişim ve Policy kontrolü</li>
            <li>🔍 Akıllı arama, filtreleme ve pagination</li>
            <li>💾 CSRF + Axios güvenlik doğrulaması</li>
          </ul>
        </div>

        <!-- Student Paneli -->
        <div
          class="bg-white/80 dark:bg-white/10 backdrop-blur-lg border border-emerald-400/30 dark:border-emerald-600/30 
                rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:scale-[1.02]"
        >
          <h3 class="text-lg font-bold mb-3 text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
            👨‍🎓 Student Paneli
          </h3>
          <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
            <li>📄 Kurs listesini ve içerikleri görüntüleme</li>
            <li>🧭 Ders videolarına erişim (YouTube URL destekli)</li>
            <li>🟢 Kurslara kayıt olma / kayıttan çıkma</li>
            <li>📚 “MyCourses” sayfası (kişisel kurs arşivi)</li>
            <li>📆 Kurs tarih, eğitmen ve ilerleme bilgisi</li>
            <li>💬 Modern form yapıları ve doğrulama uyarıları</li>
            <li>🎨 Responsive, sezgisel ve animasyonlu tasarım</li>
          </ul>
        </div>

        <!-- Ortak Özellikler -->
        <div
          class="bg-white/80 dark:bg-white/10 backdrop-blur-lg border border-purple-400/30 dark:border-purple-600/30 
                rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:scale-[1.02]"
        >
          <h3 class="text-lg font-bold mb-3 text-purple-600 dark:text-purple-400 flex items-center gap-2">
            ⚙️ Ortak Özellikler
          </h3>
          <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
            <li>🌙 Light / Dark tema desteği</li>
            <li>🧠 Inertia.js + Vue 3 + Ziggy entegrasyonu</li>
            <li>🪶 v-motion + Lottie animasyon geçişleri</li>
            <li>💡 Şifre görünür/gizli butonu (🙈 / 👁️)</li>
            <li>🔒 Policy & Request bazlı erişim denetimi</li>
            <li>💾 Otomatik ilişki temizleme & güvenli silme</li>
            <li>📱 Tüm cihazlara uyumlu tasarım (responsive)</li>
            <li>⚡ Optimize edilmiş performans & lazy yükleme</li>
            <li>🔑 Laravel Breeze + Sanctum auth altyapısı</li>
            <li>🧭 RateLimiter cache koruması (yenilemeden devam)</li>
          </ul>
        </div>

        <!-- Güvenlik & Bildirim Sistemi -->
        <div
          class="bg-white/80 dark:bg-white/10 backdrop-blur-lg border border-pink-400/30 dark:border-pink-600/30 
                rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:scale-[1.02]"
        >
          <h3 class="text-lg font-bold mb-3 text-pink-600 dark:text-pink-400 flex items-center gap-2">
            💜 Güvenlik & Bildirim Sistemi
          </h3>
          <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
            <li>📨 Şifremi Unuttum & Şifre Yenileme (token + toast + Lottie)</li>
            <li>🔑 Şifre doğrulama: min 8 karakter, * ve _ desteği</li>
            <li>⏳ Rate Limiter: fazla isteklerde otomatik kilit</li>
            <li>🧩 LocalStorage ile süre & form yönetimi</li>
            <li>📣 Dinamik Toast sistemi (başarı / hata / uyarı / bilgi)</li>
            <li>🎞️ Lottie hourglass animasyonu ile bekleme efekti</li>
            <li>🧠 403 / 404 / 419 / 422 / 500 hata yönetimi</li>
            <li>🔐 CSRF Token & güvenli Axios post işlemleri</li>
            <li>💬 Şifre sıfırlama sonrası login engelleme</li>
            <li>🧾 Güvenli profil & parola güncelleme sistemi</li>
          </ul>
        </div>
      </section>

      <footer class="text-xs mt-16 text-gray-500 dark:text-gray-400">
        © 2025 LaravelCourses — Laravel 12 • Vue 3 • Inertia
      </footer>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { onMounted } from 'vue'


const props = defineProps({
  auth: Object,
})

onMounted(() => {
  if (sessionStorage.getItem('passwordResetSuccess')) {
    window.showToast('Hoş geldin 🎉 Şifren başarıyla değiştirildi!', 'success')
    sessionStorage.removeItem('passwordResetSuccess')
  }
})

// Rol tabanlı yönlendirme
const goToDashboard = () => {
  const role = props.auth?.user?.role

  if (role === 'admin') {
    router.visit(route('admin.dashboard'))
  } else if (role === 'student') {
    router.visit(route('student.courses.index'))
  } else {
    router.visit(route('home'))
  }
}
</script>
