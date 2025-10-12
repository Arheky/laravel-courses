<template>
  <GuestLayout>
    <div
      class="min-h-screen flex flex-col items-center justify-center text-center
             bg-gray-50 dark:bg-gray-900 transition-colors duration-300 px-6"
    >
      <motion tag="div"
        class="flex flex-col items-center space-y-6"
        :initial="{ opacity: 0, y: 30, scale: 0.95 }"
        :enter="{ opacity: 1, y: 0, scale: 1 }"
        transition="ease-in-out"
      >
        <!-- İkon -->
        <div class="p-4 rounded-full" :class="iconBgClass">
          <component :is="icon" class="w-10 h-10" :class="iconColorClass" />
        </div>

        <!-- Hata kodu -->
        <h1 class="text-[5rem] sm:text-[7rem] font-extrabold" :class="colorClass">
          {{ code }}
        </h1>

        <!-- Başlık -->
        <h2 class="text-2xl sm:text-3xl font-semibold text-gray-800 dark:text-gray-100">
          {{ title }}
        </h2>

        <!-- Mesaj -->
        <p class="text-gray-600 dark:text-gray-400 max-w-md leading-relaxed">
          {{ message }}
        </p>

        <!-- Geri dön butonu -->
        <Link
          :href="route('home')"
          class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-lg
                 font-medium text-white shadow transition-all duration-200"
          :class="buttonClass"
        >
          <svg xmlns="http://www.w3.org/2000/svg"
               class="h-5 w-5" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 19l-7-7 7-7" />
          </svg>
          Ana Sayfaya Dön
        </Link>
      </motion>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link } from '@inertiajs/vue3'

// Heroicons
import {
  ExclamationTriangleIcon as AlertTriangle,
  ShieldExclamationIcon as ShieldOff,
  ServerIcon as ServerCrash,
  LockClosedIcon as Lock
} from '@heroicons/vue/24/outline'

const props = defineProps({
  code: { type: [String, Number], required: true },
  title: { type: String, required: true },
  message: { type: String, required: true },
})

// Renk ve ikon eşleşmeleri
const colorMap = {
  404: 'text-indigo-500 dark:text-indigo-400',
  403: 'text-yellow-500 dark:text-yellow-400',
  500: 'text-red-500 dark:text-red-400',
  401: 'text-blue-500 dark:text-blue-400',
}

const buttonMap = {
  404: 'bg-indigo-600 hover:bg-indigo-700',
  403: 'bg-yellow-500 hover:bg-yellow-600',
  500: 'bg-red-600 hover:bg-red-700',
  401: 'bg-blue-600 hover:bg-blue-700',
}

const iconMap = {
  404: AlertTriangle,
  403: ShieldOff,
  500: ServerCrash,
  401: Lock,
}

const iconBgMap = {
  404: 'bg-indigo-100 dark:bg-indigo-900/40',
  403: 'bg-yellow-100 dark:bg-yellow-900/40',
  500: 'bg-red-100 dark:bg-red-900/40',
  401: 'bg-blue-100 dark:bg-blue-900/40',
}

const iconColorMap = {
  404: 'text-indigo-500 dark:text-indigo-400',
  403: 'text-yellow-500 dark:text-yellow-400',
  500: 'text-red-500 dark:text-red-400',
  401: 'text-blue-500 dark:text-blue-400',
}

const colorClass = colorMap[props.code] || 'text-gray-500'
const buttonClass = buttonMap[props.code] || 'bg-gray-500 hover:bg-gray-600'
const icon = iconMap[props.code] || AlertTriangle
const iconBgClass = iconBgMap[props.code] || 'bg-gray-200 dark:bg-gray-800'
const iconColorClass = iconColorMap[props.code] || 'text-gray-500'
</script>

<style scoped>
h1 {
  letter-spacing: -2px;
  line-height: 1;
}
</style>
