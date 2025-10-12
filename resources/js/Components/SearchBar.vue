<template>
  <form @submit.prevent="submit" class="flex items-center gap-2">
    <input v-model="q" type="text" placeholder="Ara..." class="w-56 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    <UiButton type="submit">Ara</UiButton>
    <button type="button" @click="reset" class="text-sm text-gray-500 hover:underline">Temizle</button>
  </form>
</template>
<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import UiButton from '@/Components/Ui/UiButton.vue';

const props = defineProps({ url: { type: String, required: true }, initial: String });
const q = ref(props.initial || '');

function submit() {
  router.get(props.url, { search: q.value }, { preserveState: true, replace: true });
}
function reset() {
  q.value = '';
  router.get(props.url, {}, { preserveState: true, replace: true });
}
</script>
