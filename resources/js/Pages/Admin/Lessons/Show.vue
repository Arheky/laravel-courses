<template>
  <AdminLayout>
    <div class="max-w-4xl mx-auto py-10 px-6 space-y-6">
      <!--  Başlık + Geri Butonu -->
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
          📘 {{ lesson.title }}
        </h1>
        <Link
          :href="route('admin.lessons.index')"
          class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm"
        >
          ← Geri
        </Link>
      </div>

      <!--  Ders Bilgileri -->
      <div
        class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 space-y-3"
      >
        <p class="text-gray-700 dark:text-gray-300">
          <strong>Kurs:</strong>
          {{ lesson.course?.title || 'Bilinmiyor' }}
        </p>

        <p class="text-gray-700 dark:text-gray-300">
          <strong>Video:</strong>
          <span v-if="lesson.video_url">
            <a
              :href="lesson.video_url"
              target="_blank"
              class="text-indigo-600 hover:underline dark:text-indigo-400"
            >
              🎬 İzle
            </a>
          </span>
          <span v-else class="text-gray-500 dark:text-gray-400">Video bağlantısı yok.</span>
        </p>

        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
          <strong>İçerik:</strong>
          <br />
          {{ lesson.content || 'İçerik bulunamadı.' }}
        </p>
      </div>

      <!--  Aksiyonlar -->
      <div class="flex justify-end gap-3">
        <Link
          :href="route('admin.lessons.edit', lesson)"
          class="px-4 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium transition"
        >
          ✏️ Düzenle
        </Link>

        <button
          @click="confirmDelete(lesson)"
          class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition"
        >
          🗑️ Sil
        </button>
      </div>

      <!--  Silme Onay Modal -->
      <ConfirmModal
        v-if="confirm.visible"
        :title="confirm.title"
        :message="confirm.message"
        :visible="confirm.visible"
        @confirm="performDelete"
        @cancel="confirm.visible = false"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { inertiaDelete } from '@/Helpers/inertiaActions'
import { ref } from 'vue'

/**
 *  Sayfa verileri
 */
const { props } = usePage()
const lesson = props.lesson

/**
 *  Silme işlemi onayı
 */
const confirm = ref({
  visible: false,
  title: '',
  message: '',
  lesson: null,
})

/**
 *  Modal aç
 */
function confirmDelete(lesson) {
  confirm.value = {
    visible: true,
    title: `${lesson.title} dersini silmek istiyor musun?`,
    message: 'Bu işlem geri alınamaz. Ders kalıcı olarak silinecektir.',
    lesson,
  }
}

/**
 *  Silme işlemini gerçekleştir
 */
function performDelete() {
  inertiaDelete(route('admin.lessons.destroy', confirm.value.lesson), {
    onSuccess: () => {
      confirm.value.visible = false

      //  Başarı bildirimi
      window.dispatchEvent(
        new CustomEvent('show-toast', {
          detail: { message: '🗑️ Ders başarıyla silindi!', type: 'success' },
        })
      )

      //  Listeye geri dön
      router.visit(route('admin.lessons.index'))
    },
  })
}
</script>

<style scoped>
/* Genel buton stilleri */
.btn {
  @apply px-4 py-2 rounded-lg font-medium transition;
}
.btn-edit {
  @apply bg-yellow-500 hover:bg-yellow-600 text-white;
}
.btn-delete {
  @apply bg-red-600 hover:bg-red-700 text-white;
}
</style>
