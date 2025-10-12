<template>
  <!-- 🔹 Sayfa geçişleri -->
  <div
    v-if="paginationStore.links.length > 1"
    class="flex justify-center items-center flex-wrap gap-1 mt-8 select-none"
  >
    <button
      v-for="(link, i) in paginationStore.links"
      :key="i"
      v-html="sanitizeLabel(link.label)"
      :disabled="!link.url"
      @click="link.url && paginationStore.goTo(link.url)"
      class="min-w-[36px] h-9 px-3 text-sm rounded-lg border transition-all duration-200
             flex items-center justify-center font-medium"
      :class="[
        link.active
          ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
          : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-gray-700',
        !link.url && 'opacity-50 cursor-not-allowed hover:bg-transparent'
      ]"
    />
  </div>
</template>

<script setup>
import { paginationStore } from '@/Stores/paginationStore'

/**
 * 🔹 Sayfa link etiketini düzenler
 * Laravel pagination label’ları bazen “&laquo; Previous” şeklinde gelir.
 */
function sanitizeLabel(label) {
  return label
    .replace('&laquo;', '←')
    .replace('&raquo;', '→')
    .replace('Previous', 'Önceki')
    .replace('Next', 'Sonraki')
}
</script>

<style scoped>
button {
  @apply transition ease-in-out duration-200;
}
</style>
