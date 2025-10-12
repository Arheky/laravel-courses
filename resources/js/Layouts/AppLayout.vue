<template>
  <div class="min-h-screen bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-gray-100 flex flex-col transition-colors">
    <!-- PAGE CONTENT -->
    <main class="flex-1">
      <slot />
    </main>
  </div>
</template>

<script setup>
</script>

<style scoped>
html {
  scroll-behavior: smooth;
}
</style>
