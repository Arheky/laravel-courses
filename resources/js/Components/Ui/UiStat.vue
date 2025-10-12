<template>
  <div class="rounded-2xl bg-gradient-to-br from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 border border-gray-200 dark:border-gray-800 p-5">
    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ label }}</div>
    <div class="mt-1 text-3xl font-semibold">{{ value }}</div>
    <div v-if="hint" class="mt-1 text-xs text-gray-500">{{ hint }}</div>
  </div>
</template>
<script setup>
defineProps({ label: String, value: [String, Number], hint: String });
</script>
