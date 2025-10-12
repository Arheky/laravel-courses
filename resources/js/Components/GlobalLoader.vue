<template>
  <transition name="fade">
    <div
      v-if="visible"
      class="fixed inset-0 z-[9999] flex items-center justify-center
             bg-white/70 dark:bg-black/60 backdrop-blur-sm"
    >
      <div class="flex flex-col items-center gap-4 animate-fade-in">
        <!-- 🔹 Dönen çember -->
        <div
          class="h-12 w-12 border-[4px] border-transparent border-t-indigo-500 
                 dark:border-t-indigo-400 rounded-full animate-spin
                 bg-gradient-to-r from-indigo-500/20 to-indigo-300/30"
        ></div>

        <!-- 🔹 Dinamik Mesaj -->
        <p
          class="flex items-center gap-2 text-gray-700 dark:text-gray-200 animate-pulse-slow"
        >
          <svg
            class="w-4 h-4 animate-spin"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              stroke-width="4"
              d="M4 12a8 8 0 018-8v8z"
            ></path>
          </svg>

          {{ message }}
        </p>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const visible = ref(false)
const message = ref('İşlem yapılıyor...')

function showLoader(event) {
  const method = event?.detail?.method || 'get'

  // 🔹 HTTP method’a göre mesaj
  switch (method.toLowerCase()) {
    case 'post':
      message.value = 'Kayıt ekleniyor...'
      break
    case 'put':
    case 'patch':
      message.value = 'Veri güncelleniyor...'
      break
    case 'delete':
      message.value = 'Silme işlemi yapılıyor...'
      break
    default:
      message.value = 'Sayfa yükleniyor...'
      break
  }

  visible.value = true
}

function hideLoader() {
  visible.value = false
}

onMounted(() => {
  window.addEventListener('loading:start', showLoader)
  window.addEventListener('loading:end', hideLoader)
})
onUnmounted(() => {
  window.removeEventListener('loading:start', showLoader)
  window.removeEventListener('loading:end', hideLoader)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes fadeIn {
  from {
    transform: scale(0.96);
    opacity: 0.8;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
.animate-fade-in {
  animation: fadeIn 0.4s ease-in-out;
}

@keyframes pulseSlow {
  0%,
  100% {
    opacity: 0.65;
  }
  50% {
    opacity: 1;
  }
}
.animate-pulse-slow {
  animation: pulseSlow 1.8s ease-in-out infinite;
}
</style>
