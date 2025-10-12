<template>
  <div
    class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col transition-colors"
  >
    <!-- Navbar -->
    <header
      class="bg-white/80 dark:bg-gray-800/80 backdrop-blur border-b border-gray-200 dark:border-gray-700 sticky top-0 z-[100] shadow-md overflow-visible"
    >
      <nav
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16 relative"
      >
        <!-- Sol Menü -->
        <div class="flex items-center space-x-6">
          <h1 class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
            Öğrenci Paneli
          </h1>

          <!-- Öğrenci Menüsü -->
          <Link :href="route('student.courses.index')" class="nav-link">Tüm Kurslar</Link>
          <Link :href="route('student.mycourses.index')" class="nav-link">Kurslarım</Link>
        </div>

        <!-- Sağ Menü -->
        <div class="flex items-center gap-3 relative">
          <!-- Tema Butonu + Tooltip -->
          <div
            class="relative"
            @mouseenter="showTip = true"
            @mouseleave="showTip = false"
          >
            <button
              @click="toggleTheme"
              class="relative w-10 h-10 flex items-center justify-center rounded-full
                     bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-yellow-300
                     shadow-md hover:shadow-lg hover:scale-105
                     transition-all duration-300 active:scale-95"
            >
              <span v-if="isDark" class="text-lg">🌙</span>
              <span v-else class="text-lg">☀️</span>
            </button>

            <transition name="fade">
              <div
                v-if="showTip"
                class="absolute top-12 right-1/2 translate-x-1/2 whitespace-nowrap
                       bg-gray-800/90 dark:bg-gray-100 text-gray-100 dark:text-gray-800
                       text-xs px-3 py-1.5 rounded-lg shadow-md backdrop-blur-md border border-gray-600/40"
              >
                {{ isDark ? '🌙 Dark Mode Aktif' : '☀️ Light Mode Aktif' }}
              </div>
            </transition>
          </div>

          <!-- Kullanıcı Dropdown -->
          <div class="relative" ref="dropdownRef">
            <button
              @click.stop="toggleDropdown"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-sm font-medium"
            >
              <span>{{ userStore.user.value?.name || 'Öğrenci Kullanıcı' }}</span>
              <svg
                class="w-4 h-4 transform transition-transform"
                :class="{ 'rotate-180': dropdownOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Açılır Menü -->
            <div
              v-show="dropdownOpen"
              class="absolute right-0 mt-2 w-44 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-2xl py-2 z-[9999]"
            >
              <Link
                :href="route('profile.show')"
                class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200"
              >
                Profilim
              </Link>

              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                Çıkış Yap
              </Link>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Sayfa İçeriği -->
    <main class="flex-1 px-6 py-8 max-w-7xl mx-auto w-full">
      <slot />
    </main>

    <!-- Toast Bildirimi -->
    <transition name="toast-fade">
      <div
        v-if="toast.visible"
        :class="[ 
          'fixed top-6 right-6 z-[99999] px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 text-sm font-medium transition-all backdrop-blur-lg border',
          toast.type === 'success'
            ? 'bg-green-500/90 text-white border-green-400 shadow-green-300/40'
            : toast.type === 'error'
            ? 'bg-red-500/90 text-white border-red-400 shadow-red-300/40'
            : 'bg-indigo-500/90 text-white border-indigo-400 shadow-indigo-300/40',
        ]"
      >
        <span class="text-lg">
          <template v-if="toast.type === 'success'">✅</template>
          <template v-else-if="toast.type === 'error'">❌</template>
          <template v-else>ℹ️</template>
        </span>
        <span>{{ toast.message }}</span>
      </div>
    </transition>

    <!-- Footer -->
    <footer
      class="text-center text-xs text-gray-500 dark:text-gray-400 py-6 border-t border-gray-200 dark:border-gray-700"
    >
      © 2025 LaravelCourses — Öğrenci Panel
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { userStore } from '@/Stores/userStore'

/* Kullanıcı Bilgisi */
const page = usePage()
const inertiaUser = page.props.auth?.user || { name: 'Öğrenci Kullanıcı' }
userStore.setUser(inertiaUser)

/* Tema Sistemi */
const isDark = ref(false)
const showTip = ref(false)

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark')
    isDark.value = true
  }
})

function toggleTheme() {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

/* Dropdown */
const dropdownOpen = ref(false)
const dropdownRef = ref(null)
function toggleDropdown() {
  dropdownOpen.value = !dropdownOpen.value
}
function handleClickOutside(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) dropdownOpen.value = false
}
onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

/* Toast Bildirimleri */
const toast = ref({ visible: false, message: '', type: 'success' })
function showToast(message, type = 'success') {
  toast.value = { visible: true, message, type }
  setTimeout(() => (toast.value.visible = false), 3500)
}
onMounted(() => window.addEventListener('show-toast', e => showToast(e.detail.message, e.detail.type)))
onBeforeUnmount(() => window.removeEventListener('show-toast', showToast))
</script>

<style scoped>
.nav-link {
  @apply text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm font-medium transition;
}

/* Tooltip Animasyonu */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Toast Fade */
.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.5s ease;
}
.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(-15px) scale(0.95);
}
</style>
