<template>
  <GuestLayout>
    <div
      class="min-h-screen flex flex-col items-center justify-center text-center
             bg-gray-50 dark:bg-gray-900 transition-colors duration-300 px-6"
    >
      <motion.div
        class="flex flex-col items-center space-y-6"
        :initial="{ opacity: 0, y: 30, scale: 0.95 }"
        :enter="{ opacity: 1, y: 0, scale: 1 }"
        transition="ease-in-out"
      >
        <!-- İkon -->
        <div class="p-4 rounded-full bg-blue-100 dark:bg-blue-900/40">
          <svg xmlns="http://www.w3.org/2000/svg"
               class="w-10 h-10 text-blue-500 dark:text-blue-400"
               fill="none" viewBox="0 0 24 24" stroke="currentColor"
               stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 11c0-1.657-1.343-3-3-3s-3 1.343-3 3m0 0v2a9 9 0 1018 0v-2m-6 0v2m-6 0v2m0-2a9 9 0 0118 0v2" />
          </svg>
        </div>

        <!-- Kod -->
        <h1 class="text-[5rem] sm:text-[7rem] font-extrabold text-blue-500 dark:text-blue-400">
          401
        </h1>

        <!-- Başlık -->
        <h2 class="text-2xl sm:text-3xl font-semibold text-gray-800 dark:text-gray-100">
          Oturum Süresi Doldu
        </h2>

        <!-- Mesaj -->
        <p class="text-gray-600 dark:text-gray-400 max-w-md leading-relaxed">
          Oturumunuz sona erdi. Güvenliğiniz için yeniden giriş yapmanız gerekiyor.
        </p>

        <!-- Giriş yap butonu -->
        <div class="flex gap-3 mt-4">
          <Link
            :href="route('login')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg
                   bg-blue-600 hover:bg-blue-700 text-white font-medium
                   shadow transition-all duration-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 12H3m12 0l-4-4m4 4l-4 4m8-12v16" />
            </svg>
            Tekrar Giriş Yap
          </Link>

          <Link
            :href="route('home')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg
                   bg-gray-500 hover:bg-gray-600 text-white font-medium
                   shadow transition-all duration-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 19l-7-7 7-7" />
            </svg>
            Ana Sayfaya Dön
          </Link>
        </div>
      </motion.div>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link } from '@inertiajs/vue3'
import { MotionPlugin } from '@vueuse/motion'
</script>

<style scoped>
h1 {
  letter-spacing: -2px;
  line-height: 1;
}
</style>
