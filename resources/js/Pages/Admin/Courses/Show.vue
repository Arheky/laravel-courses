<template>
  <AdminLayout>
    <!-- Silme Onayı Modal -->
    <ConfirmModal
      v-if="confirm.visible"
      :title="confirm.title"
      :message="confirm.message"
      :visible="confirm.visible"
      @confirm="performDelete"
      @cancel="confirm.visible = false"
    />

    <div class="max-w-5xl mx-auto py-10 px-6 space-y-6">
      <!-- Başlık + Geri Butonu -->
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
          {{ course.title }}
        </h1>

        <Link
          :href="route('admin.courses.index')"
          class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm"
        >
          ← Geri
        </Link>
      </div>

      <!-- Kurs Bilgileri -->
      <div
        class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700"
      >
        <p class="text-gray-700 dark:text-gray-300 mb-2">
          <strong>Açıklama:</strong>
          {{ course.description || 'Açıklama bulunamadı.' }}
        </p>
        <p class="text-gray-700 dark:text-gray-300 mb-2">
          <strong>Eğitmen:</strong> {{ course.instructor || 'Belirtilmemiş' }}
        </p>
        <p class="text-gray-700 dark:text-gray-300">
          <strong>Başlangıç Tarihi:</strong> {{ course.start_date || 'Belirtilmemiş' }}
        </p>

        <Link
          :href="route('admin.courses.students', { course: course.id })"
          class="inline-block mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm"
        >
          👨‍🎓 Kayıtlı Öğrenciler
        </Link>
      </div>

      <!-- Ders Listesi -->
      <div>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
          📚 Dersler
        </h2>

        <div v-if="lessons.length" class="grid md:grid-cols-2 gap-6">
          <div
            v-for="lesson in lessons"
            :key="lesson.id"
            class="p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow relative hover:shadow-md transition"
          >
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
              {{ lesson.title }}
            </h3>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
              {{ lesson.content?.slice(0, 100) || 'İçerik bulunamadı.' }}
            </p>

            <a
              v-if="lesson.video_url"
              :href="lesson.video_url"
              target="_blank"
              class="text-indigo-500 hover:underline text-sm"
            >
              🎬 Videoyu İzle
            </a>

            <button
              @click="confirmDelete(lesson)"
              class="absolute top-3 right-3 text-xs bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded transition"
            >
              Sil
            </button>
          </div>
        </div>

        <p v-else class="text-gray-500 dark:text-gray-400 text-center mt-6">
          Henüz ders bulunamadı.
        </p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { inertiaDelete } from '@/Helpers/inertiaActions'

/* -------------------------------
 * Inertia Props
 * ------------------------------- */
const { props } = usePage()
const course = props.course
const lessons = ref(course.lessons || [])

/* -------------------------------
 * Silme Modal Durumu
 * ------------------------------- */
const confirm = ref({
  visible: false,
  title: '',
  message: '',
  lesson: null,
})

/* Silme Onayı Aç */
function confirmDelete(lesson) {
  confirm.value = {
    visible: true,
    title: `${lesson.title} dersini silmek istiyor musun?`,
    message: 'Bu işlem geri alınamaz. Ders kalıcı olarak silinecektir.',
    lesson,
  }
}

/* Silme İşlemi */
function performDelete() {
  if (!confirm.value.lesson) return

  inertiaDelete(route('admin.lessons.destroy', { lesson: confirm.value.lesson.id }), {
    onSuccess: () => {
      lessons.value = lessons.value.filter(
        (l) => l.id !== confirm.value.lesson.id
      )
      confirm.value.visible = false

      window.dispatchEvent(
        new CustomEvent('show-toast', {
          detail: { message: '🗑️ Ders başarıyla silindi!', type: 'success' },
        })
      )
    },
  })
}
</script>

<style scoped>
button {
  @apply transition;
}
</style>
