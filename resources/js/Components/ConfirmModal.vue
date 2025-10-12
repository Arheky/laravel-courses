<template>
  <div
    v-if="visible"
    class="fixed inset-0 bg-black/40 flex items-center justify-center z-[999]"
  >
    <div
      class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-[90%] max-w-sm text-center"
    >
      <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">
        {{ title }}
      </h3>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">
        {{ message }}
      </p>

      <div class="flex justify-center gap-3">
        <button
          @click="confirm"
          class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium"
        >
          {{ confirmText }}
        </button>
        <button
          @click="cancel"
          class="px-4 py-2 rounded-lg bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium"
        >
          {{ cancelText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  title: { type: String, default: 'Emin misiniz?' },
  message: { type: String, default: 'Bu işlem geri alınamaz.' },
  confirmText: { type: String, default: 'Evet, Sil' },
  cancelText: { type: String, default: 'İptal' },
})

const emit = defineEmits(['confirm', 'cancel'])
const visible = ref(true)

function confirm() {
  emit('confirm')
  visible.value = false
}
function cancel() {
  emit('cancel')
  visible.value = false
}
</script>
