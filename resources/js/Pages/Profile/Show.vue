<template>
  <component :is="layout">
    <div class="max-w-5xl mx-auto space-y-8 py-10 px-4">
      <!-- Kullanıcı Bilgileri -->
      <div class="bg-white/70 dark:bg-white/10 backdrop-blur-lg border dark:border-gray-700 rounded-2xl shadow p-6">
        <h2 class="text-xl font-semibold mb-4">👤 Kullanıcı Bilgileri</h2>

        <form @submit.prevent="updateProfile" class="space-y-4">
          <div>
            <label class="block text-sm mb-1">Ad Soyad</label>
            <input v-model="form.name" class="input" placeholder="Adınızı girin" />
          </div>

          <div>
            <label class="block text-sm mb-1">E-posta</label>
            <input v-model="form.email" type="email" class="input" placeholder="E-posta adresiniz" />
          </div>

          <div v-if="form.hasErrors" class="text-sm text-red-400" v-for="(e, k) in form.errors" :key="k">
            {{ e }}
          </div>

          <div class="flex justify-end">
            <button class="btn-primary" :disabled="form.processing">Güncelle</button>
          </div>
        </form>
      </div>

      <!-- Şifre Güncelle -->
      <div class="bg-white/70 dark:bg-white/10 backdrop-blur-lg border dark:border-gray-700 rounded-2xl shadow p-6">
        <h2 class="text-xl font-semibold mb-4">🔑 Şifre Güncelle</h2>

        <form @submit.prevent="updatePassword" class="space-y-4">
          <!-- Mevcut Şifre -->
          <div>
            <label class="block text-sm mb-1">Mevcut Şifre</label>
            <div class="relative">
              <input
                v-model="pass.current_password"
                :type="showCurrent ? 'text' : 'password'"
                class="input pr-10"
                placeholder="••••••••"
              />
              <button
                type="button"
                class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                @click="showCurrent = !showCurrent"
              >
                {{ showCurrent ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <!-- Yeni Şifre -->
          <div>
            <label class="block text-sm mb-1">Yeni Şifre</label>
            <div class="relative">
              <input
                v-model="pass.password"
                :type="showPassword ? 'text' : 'password'"
                class="input pr-10"
                placeholder="En az 8 karakter"
              />
              <button
                type="button"
                class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                @click="showPassword = !showPassword"
              >
                {{ showPassword ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <!-- Yeni Şifre Tekrar -->
          <div>
            <label class="block text-sm mb-1">Yeni Şifre (Tekrar)</label>
            <div class="relative">
              <input
                v-model="pass.password_confirmation"
                :type="showConfirm ? 'text' : 'password'"
                class="input pr-10"
                placeholder="Şifreyi tekrar girin"
              />
              <button
                type="button"
                class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                @click="showConfirm = !showConfirm"
              >
                {{ showConfirm ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <div v-if="pass.hasErrors" class="text-sm text-red-400" v-for="(e, k) in pass.errors" :key="k">
            {{ e }}
          </div>

          <div class="flex justify-end">
            <button class="btn-success" :disabled="pass.processing">Şifreyi Güncelle</button>
          </div>
        </form>
      </div>

      <!-- Kurslarım (Yalnızca Student için) -->
      <div
        v-if="user.role === 'student' && enrolledCourses.length"
        class="bg-white/70 dark:bg-white/10 backdrop-blur-lg border dark:border-gray-700 rounded-2xl shadow p-6"
      >
        <h2 class="text-xl font-semibold mb-6">📚 Kurslarım</h2>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="course in enrolledCourses"
            :key="course.id"
            class="p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow hover:shadow-lg transition transform hover:scale-[1.02]"
          >
            <h3 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-1">{{ course.title }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3 mb-2">
              {{ course.description || 'Açıklama bulunamadı.' }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">👨‍🏫 {{ course.instructor }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">📅 {{ course.start_date }}</p>
          </div>
        </div>

        <p v-if="!enrolledCourses.length" class="text-sm text-gray-500 dark:text-gray-400 mt-4">
          Henüz hiçbir kursa kayıt olmadınız.
        </p>
      </div>
    </div>

    <!-- Toast -->
    <transition name="fade">
      <div
        v-if="toast.visible"
        :class="[
          'fixed bottom-6 right-6 px-4 py-2.5 rounded-lg shadow-lg text-white font-medium transition backdrop-blur-lg',
          toast.type === 'success'
            ? 'bg-green-600/90'
            : toast.type === 'error'
            ? 'bg-red-600/90'
            : 'bg-blue-600/90',
        ]"
      >
        {{ toast.message }}
      </div>
    </transition>
  </component>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { reactive, computed, ref } from 'vue'
import { userStore } from '@/Stores/userStore'

// Toast
const toast = reactive({ visible: false, message: '', type: 'success' })
function showToast(message, type = 'success') {
  toast.message = message
  toast.type = type
  toast.visible = true
  setTimeout(() => (toast.visible = false), 3000)
}

// Kullanıcı & Kurslar
const page = usePage()
const user = page.props.auth.user
const enrolledCourses = page.props.enrolledCourses || []

// Layout seçimi
const layout = computed(() => (user.role === 'admin' ? AdminLayout : StudentLayout))

// Formlar
const form = useForm({ name: user.name, email: user.email })
const pass = useForm({ current_password: '', password: '', password_confirmation: '' })

// Şifre Görünürlükleri
const showCurrent = ref(false)
const showPassword = ref(false)
const showConfirm = ref(false)

// Profil Güncelle
function updateProfile() {
  form.put(route('profile.update'), {
    onSuccess: () => {
      router.reload({
        only: ['auth'],
        onSuccess: (page) => userStore.setUser(page.props.auth.user),
      })
      showToast('Profil bilgileri güncellendi ✅')
    },
    onError: () => showToast('Profil güncellenemedi ❌', 'error'),
  })
}

// Şifre Güncelle
function updatePassword() {
  if (!pass.current_password || !pass.password || !pass.password_confirmation)
    return showToast('Tüm alanları doldurmalısınız 🚫', 'warning')

  // Şifre kuralı: sadece harf, rakam, _ ve * izinli
  const passwordRegex = /^[a-zA-ZığüşöçİĞÜŞÖÇ0-9_*]+$/

  if (pass.password.length < 8)
    return showToast('Yeni şifre en az 8 karakter olmalı 🔐', 'warning')

  if (!passwordRegex.test(pass.password))
    return showToast('Şifre sadece harf, rakam, _ ve * karakterlerinden oluşabilir ❌', 'error')

  if (pass.password !== pass.password_confirmation)
    return showToast('Yeni şifreler eşleşmiyor ❌', 'error')

  pass.put(route('profile.password'), {
    onSuccess: () => {
      pass.reset()
      showToast('Şifre başarıyla güncellendi 🔒', 'success')
    },
    onError: (errors) => {
      if (errors.current_password)
        showToast('Mevcut şifre hatalı ⚠️', 'error')
      else
        showToast('Şifre güncellenemedi ❌', 'error')
    },
  })
}
</script>

<style scoped>
.input {
  @apply w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
         text-gray-900 dark:text-gray-100 px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 transition;
}
.btn-primary {
  @apply px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition disabled:opacity-70 disabled:cursor-not-allowed;
}
.btn-success {
  @apply px-4 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium transition disabled:opacity-70 disabled:cursor-not-allowed;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
