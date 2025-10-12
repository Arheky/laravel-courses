<template>
  <div v-if="links && links.length > 3" class="mt-6 flex flex-wrap gap-2">
    <template v-for="(link, idx) in links" :key="idx">
      <Component
        :is="link.url ? 'a' : 'span'"
        :href="link.url || undefined"
        class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 dark:border-gray-700"
        :class="{
          'bg-blue-600 text-white border-blue-600': link.active,
          'text-gray-500 pointer-events-none': !link.url,
        }"
        v-html="link.label"
      />
    </template>
  </div>
</template>
<script setup>
defineProps({ links: Array });
</script>
