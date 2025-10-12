<template>
  <AdminLayout>
    <div class="max-w-xl mx-auto py-10 px-6">
      <!-- Başlık + Geri Butonu -->
      <div class="flex justify-between items-center mb-6">
        <h1
          class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2"
        >
          {{ isEdit ? '✏️ Dersi Düzenle' : '➕ Yeni Ders Ekle' }}
        </h1>
        <Link :href="route('admin.lessons.index')" class="btn-secondary">← Geri</Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-5">
        <!-- Kurs Alanı -->
        <div v-if="!isEdit">
          <label class="label">Kurs</label>
          <select v-model="form.course_id" class="input">
            <option disabled value="">Kurs seçin</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.title }}
            </option>
          </select>
          <p v-if="form.errors.course_id" class="error">{{ form.errors.course_id }}</p>
        </div>

        <div v-else>
          <label class="label">Kurs</label>
          <input
            type="text"
            class="input bg-gray-100 dark:bg-gray-700 cursor-not-allowed"
            :value="lesson.course?.title || 'Bilinmiyor'"
            disabled
          />
        </div>

        <!-- Ders Başlığı -->
        <div>
          <label class="label">Ders Başlığı</label>
          <input
            v-model="form.title"
            type="text"
            class="input"
            placeholder="Ders başlığını girin"
          />
          <p v-if="form.errors.title" class="error">{{ form.errors.title }}</p>
        </div>

        <!-- Video URL -->
        <div>
          <label class="label">Video URL</label>
          <input
            v-model="form.video_url"
            type="text"
            class="input"
            placeholder="Video bağlantısı (isteğe bağlı)"
          />
          <p v-if="form.errors.video_url" class="error">{{ form.errors.video_url }}</p>
        </div>

        <!-- İçerik -->
        <div>
          <label class="label">İçerik</label>
          <textarea
            v-model="form.content"
            rows="5"
            class="input"
            placeholder="Ders içeriğini buraya yazın..."
          ></textarea>
          <p v-if="form.errors.content" class="error">{{ form.errors.content }}</p>
        </div>

        <!-- Butonlar -->
        <div class="flex justify-end gap-3 pt-4">
          <Link :href="route('admin.lessons.index')" class="btn-secondary">İptal</Link>
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ isEdit ? 'Güncelle' : 'Kaydet' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm, usePage, router } from '@inertiajs/vue3'
import { inertiaFormPost, inertiaFormPut } from '@/Helpers/inertiaActions'

/**
 * Sayfa verileri
 */
const { props } = usePage()
const lesson = props.lesson || null
const courses = props.courses || []
const isEdit = !!lesson

/**
 *  useForm tanımı
 */
const form = useForm({
  course_id: lesson?.course_id || '',
  title: lesson?.title || '',
  video_url: lesson?.video_url || '',
  content: lesson?.content || '',
})

/**
 *  Gönderim işlemi
 */
function submit() {
  if (isEdit) {
    //  Güncelleme
    inertiaFormPut(form, route('admin.lessons.update', lesson), {
      onSuccess: () => {
        toast('✅ Ders başarıyla güncellendi!')
        router.visit(route('admin.lessons.index'))
      },
    })
  } else {
    //  Yeni kayıt
    inertiaFormPost(form, route('admin.lessons.store'), {
      onSuccess: () => {
        form.reset()
        toast('🎉 Yeni ders başarıyla eklendi!')
        router.visit(route('admin.lessons.index'))
      },
    })
  }
}

/**
 *  Toast yardımcı fonksiyonu
 */
function toast(message) {
  window.dispatchEvent(
    new CustomEvent('show-toast', {
      detail: { message, type: 'success' },
    })
  )
}
</script>

<style scoped>
.label {
  @apply block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300;
}
.input {
  @apply w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
  text-gray-900 dark:text-gray-100 px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 transition;
}
.btn-primary {
  @apply px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition;
}
.btn-secondary {
  @apply px-4 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white font-medium transition;
}
.error {
  @apply text-sm text-red-500 mt-1;
}
</style>
