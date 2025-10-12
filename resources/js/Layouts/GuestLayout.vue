<template>
  <div
    class="min-h-screen bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-gray-100 
           transition-colors duration-300 flex flex-col justify-between relative overflow-hidden"
  >
    <!-- Tema Butonu + Tooltip -->
    <div
      class="fixed top-5 right-5 z-[99999]"
      @mouseenter="showTip = true"
      @mouseleave="showTip = false"
    >
      <button
        @click="toggleTheme"
        class="relative w-11 h-11 flex items-center justify-center rounded-full
         bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-yellow-300
         shadow-lg ring-2 ring-transparent dark:ring-yellow-400/40
         hover:scale-110 transition-all duration-300 animate-soft-pulse active:scale-95 active:ring-4 active:ring-indigo-400/50"
        style="z-index:99999 !important"
      >
        <span v-if="isDark" class="text-lg">🌙</span>
        <span v-else class="text-lg">☀️</span>
      </button>

      <transition name="fade">
        <div
          v-if="showTip"
          class="absolute top-14 right-1/2 translate-x-1/2 whitespace-nowrap
                 bg-gray-800/90 dark:bg-gray-100 text-gray-100 dark:text-gray-800
                 text-xs px-3 py-1.5 rounded-lg shadow-lg backdrop-blur-md border border-gray-600/40
                 animate-float"
        >
          {{ isDark ? '🌙 Dark Mode Aktif' : '☀️ Light Mode Aktif' }}
        </div>
      </transition>
    </div>

    <!-- Aurora Background -->
    <div
      class="absolute inset-0 -z-0 bg-gradient-to-br from-indigo-100 via-transparent to-purple-200 
             dark:from-indigo-900/40 dark:via-purple-900/20 dark:to-blue-900/40 
             blur-3xl opacity-70 pointer-events-none animate-aurora"
    ></div>

    <!-- İçerik -->
    <main class="flex-1 relative z-10">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const isDark = ref(false)
const showTip = ref(false)

/* Tema yükle & kaydet */
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
</script>

<style scoped>
@keyframes soft-pulse {
  0%, 100% { transform: scale(1); box-shadow: 0 0 10px rgba(255, 223, 0, 0.3); }
  50% { transform: scale(1.05); box-shadow: 0 0 20px rgba(255, 230, 120, 0.6); }
}
.animate-soft-pulse {
  animation: soft-pulse 3s ease-in-out infinite;
}

@keyframes auroraMove {
  0% { transform: translate(-15%, -15%) rotate(0deg) scale(1); opacity: 0.6; }
  50% { transform: translate(15%, 15%) rotate(180deg) scale(1.1); opacity: 0.8; }
  100% { transform: translate(-15%, -15%) rotate(360deg) scale(1); opacity: 0.6; }
}
.animate-aurora {
  animation: auroraMove 90s ease-in-out infinite;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}
.animate-float {
  animation: float 2s ease-in-out infinite;
}
</style>
