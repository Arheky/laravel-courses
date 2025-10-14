<template>
  <AdminLayout>
    <!--  Silme Onay Modal -->
    <ConfirmModal
      v-if="confirm.visible"
      :title="confirm.title"
      :message="confirm.message"
      :visible="confirm.visible"
      @confirm="performDelete"
      @cancel="confirm.visible = false"
    />

    <div class="max-w-7xl mx-auto py-10 px-6">
      <!--  Başlık + Arama + Filtre -->
      <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
          <h1
            class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2"
          >
            📚
            <span v-if="filters.course_id">Kursa Ait Dersler</span>
            <span v-else>Tüm Dersler</span>
          </h1>

          <!--  Kurs filtresindeyken "Tüm Derslere Dön" -->
          <Link
            v-if="filters.course_id"
            :href="route('admin.lessons.index')"
            class="ml-2 text-sm px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 
                   hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition"
          >
            ← Tüm Derslere Dön
          </Link>
        </div>

        <div class="flex items-center gap-3">
          <!-- Arama -->
          <input
            v-model="search"
            type="text"
            placeholder="Ders veya kurs ara..."
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                   focus:ring focus:ring-indigo-400 outline-none w-64"
          />

          <!--  Yeni Ders -->
          <Link :href="route('admin.lessons.create')" class="btn-primary">
            ➕ Yeni Ders
          </Link>
        </div>
      </div>

      <!--  Ders Kartları -->
      <div v-if="lessonStore.lessons.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="lesson in lessonStore.lessons"
          :key="lesson.id"
          class="p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                 rounded-xl shadow hover:shadow-lg transition relative"
        >
          <h2 class="text-lg font-semibold mb-1 text-gray-900 dark:text-gray-100">
            {{ lesson.title }}
          </h2>

          <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
            {{ lesson.content?.slice(0, 80) || 'İçerik bulunamadı.' }}
          </p>

          <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
            📘 Kurs: {{ lesson.course?.title || 'Bilinmiyor' }}
          </p>

          <div class="flex justify-between items-center">
            <a
              v-if="lesson.video_url"
              :href="lesson.video_url"
              target="_blank"
              class="text-indigo-500 hover:underline text-sm"
            >
              🎬 Videoyu Aç
            </a>

            <div class="flex gap-2">
              <!--  Düzenle -->
              <Link :href="route('admin.lessons.edit', lesson)" class="btn-edit">
                Düzenle
              </Link>

              <!--  Sil -->
              <button @click="confirmDelete(lesson)" class="btn-delete">
                Sil
              </button>
            </div>
          </div>
        </div>
      </div>

      <!--  Boş Liste -->
      <p v-else class="text-gray-500 dark:text-gray-400 text-center mt-10">
        Henüz ders bulunamadı.
      </p>

      <!--  Sayfalama -->
      <Pagination />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { onMounted, ref, watch } from 'vue'
import { lessonStore } from '@/Stores/lessonStore'
import { paginationStore } from '@/Stores/paginationStore'
import Pagination from '@/Components/Pagination.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { inertiaDelete, inertiaGet } from '@/Helpers/inertiaActions'

const { props } = usePage()
const search = ref(props.filters?.search || '')
const filters = props.filters || {}
const confirm = ref({
  visible: false,
  title: '',
  message: '',
  lesson: null, 
})

//  Sayfa açıldığında dersleri ve kursları yükle
onMounted(() => {
  lessonStore.setLessons(props.lessons?.data || [])
  lessonStore.setCourses(props.courses || [])

  paginationStore.init({
    links: props.lessons?.links,
    url: route('admin.lessons.index'),
    onDataUpdate: (data) => lessonStore.setLessons(data),
  })
})

//  Arama (backend senkronize)
watchDebounced(
  search,
  (val) => {
    const term = (val ?? '').trim()
    inertiaGet(
      route('admin.courses.index'),
      { search: term || undefined },
      {
        preserveState: true,
        replace: true,
        only: ['courses', 'filters'],
        onSuccess: (page) => {
          courseStore.setCourses(page.props.courses?.data || [])
          paginationStore.setLinks(page.props.courses?.links || [])
        },
      }
    )
  },
  { debounce: 300, maxWait: 800 }
)
//  Silme işlemi için modal aç
function confirmDelete(lesson) {
  confirm.value = {
    visible: true,
    title: `${lesson.title} dersini silmek istiyor musun?`,
    message: 'Bu işlem geri alınamaz. Ders kalıcı olarak silinecek.',
    lesson, 
  }
}

//  Silme işlemi (403 hatasız)
function performDelete() {
  inertiaDelete(route('admin.lessons.destroy', confirm.value.lesson), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      lessonStore.removeLesson(confirm.value.lesson.id)
      confirm.value.visible = false

      //  Toast bildirimi
      window.dispatchEvent(
        new CustomEvent('show-toast', {
          detail: { message: '🗑️ Ders başarıyla silindi!', type: 'success' },
        })
      )

      //  Sayfalama boş kaldıysa önceki sayfaya dön
      if (lessonStore.lessons.length === 0 && paginationStore.links.length > 0) {
        const prev = paginationStore.links.find(l => l.label === '« Previous')
        if (prev && prev.url) paginationStore.goTo(prev.url)
      }
    },
  })
}
</script>

<style scoped>
.btn-primary {
  @apply px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition;
}
.btn-edit {
  @apply px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded transition;
}
.btn-delete {
  @apply px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition;
}
</style>
