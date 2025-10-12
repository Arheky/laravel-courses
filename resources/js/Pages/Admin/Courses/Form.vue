<template>
  <AdminLayout>
    <div class="max-w-xl mx-auto py-10 px-6">
      <!--  Başlık + Geri Butonu -->
      <div class="flex justify-between items-center mb-6">
        <h1
          class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2"
        >
          {{ isEdit ? '✏️ Kursu Düzenle' : '🆕 Yeni Kurs Ekle' }}
        </h1>
        <Link :href="route('admin.courses.index')" class="btn-secondary">← Geri</Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-5">
        <!-- Kurs Başlığı -->
        <div>
          <label class="label">Kurs Başlığı</label>
          <input
            v-model="form.title"
            type="text"
            class="input"
            placeholder="Kurs başlığını girin"
          />
          <p v-if="form.errors.title" class="error">{{ form.errors.title }}</p>
        </div>

        <!-- Açıklama -->
        <div>
          <label class="label">Açıklama</label>
          <textarea
            v-model="form.description"
            rows="4"
            class="input"
            placeholder="Kurs açıklaması girin"
          ></textarea>
          <p v-if="form.errors.description" class="error">{{ form.errors.description }}</p>
        </div>

        <!-- Eğitmen -->
        <div>
          <label class="label">Eğitmen</label>
          <input
            v-model="form.instructor"
            type="text"
            class="input"
            placeholder="Eğitmen adını girin"
          />
          <p v-if="form.errors.instructor" class="error">{{ form.errors.instructor }}</p>
        </div>

        <!-- Başlangıç Tarihi -->
        <div>
          <label class="label">Başlangıç Tarihi</label>
          <input
            v-model="form.start_date"
            type="date"
            class="input"
          />
          <p v-if="form.errors.start_date" class="error">{{ form.errors.start_date }}</p>
        </div>

        <!--  İşlem Butonları -->
        <div class="flex justify-end gap-3 pt-4">
          <Link :href="route('admin.courses.index')" class="btn-secondary">İptal</Link>
          <button
            type="submit"
            class="btn-primary"
            :disabled="form.processing"
          >
            {{ isEdit ? 'Güncelle' : 'Ekle' }}
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

/* -------------------------------
 *  Props ve Form Başlatma
 * ------------------------------- */
const { props } = usePage()
const course = props.course || null

// Edit mi Create mi?
const isEdit = !!course

/* -------------------------------
 *  useForm tanımı
 * ------------------------------- */
const form = useForm({
  title: course?.title || '',
  description: course?.description || '',
  instructor: course?.instructor || '',
  start_date: course?.start_date || '',
})

/* -------------------------------
 * Form Gönderim
 * ------------------------------- */
function submit() {
  if (isEdit) {
    inertiaFormPut(form, route('admin.courses.update', { course: course.id }), {
      onSuccess: () => handleSuccess('✅ Kurs başarıyla güncellendi!'),
    })
  } else {
    inertiaFormPost(form, route('admin.courses.store'), {
      onSuccess: () => {
        form.reset()
        handleSuccess('🎉 Yeni kurs başarıyla eklendi!')
      },
    })
  }
}

/* -------------------------------
 *  Başarılı İşlem Sonrası Toast + Yönlendirme
 * ------------------------------- */
function handleSuccess(message) {
  window.dispatchEvent(
    new CustomEvent('show-toast', {
      detail: { message, type: 'success' },
    })
  )

  router.visit(route('admin.courses.index'))
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
  @apply px-5 py-2.5 rounded-lg bg-gray-500 hover:bg-gray-600 text-white font-medium transition;
}
.error {
  @apply text-sm text-red-500 mt-1;
}
</style>
