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

    <div class="max-w-7xl mx-auto py-10 px-6">

      <!-- ======================== -->
      <!--  NORMAL KURS GÖRÜNÜMÜ -->
      <!-- ======================== -->
      <template v-if="!showingLessons">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
            📘 Kurslar
          </h1>

          <div class="flex items-center gap-3">
            <input
              v-model="search"
              type="text"
              placeholder="Kurs veya eğitmen ara..."
              class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                     bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                     focus:ring focus:ring-indigo-400 outline-none w-64"
            />
            <Link :href="route('admin.courses.create')" class="btn-primary">
              ➕ Yeni Kurs
            </Link>
          </div>
        </div>

        <!-- Kurs Kartları -->
        <div v-if="courseStore.courses.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="course in courseStore.courses"
            :key="course.id"
            class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                   rounded-xl shadow hover:shadow-lg transition"
          >
            <h2 class="text-lg font-semibold mb-1 text-gray-900 dark:text-gray-100">
              {{ course.title }}
            </h2>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-3">
              {{ course.description || 'Açıklama bulunamadı.' }}
            </p>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
              👨‍🏫 {{ course.instructor }}
            </p>

            <div class="flex justify-between items-center">
              <button
                @click="loadLessons(course)"
                class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium"
              >
                📚 Dersler
              </button>

              <div class="flex gap-2">
                <Link :href="route('admin.courses.show', { course: course.id })" class="btn-detail">
                  Detaylar
                </Link>
                <Link :href="route('admin.courses.edit', { course: course.id })" class="btn-edit">
                  Düzenle
                </Link>
                <button @click="confirmDelete(course)" class="btn-delete">
                  Sil
                </button>
              </div>
            </div>
          </div>
        </div>

        <p v-else class="text-gray-500 dark:text-gray-400 text-center mt-10">Henüz kurs bulunamadı.</p>

        <Pagination />
      </template>

      <!-- ============================ -->
      <!--  DERSLER (FİLTRELENMİŞ) GÖRÜNÜM -->
      <!-- ============================ -->
      <template v-else>
        <div class="flex justify-between items-center mb-6">
          <h1
            class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2"
          >
            📖 {{ activeCourse?.title }} — Dersler
          </h1>

          <!-- Kurslara Geri Dön Butonu -->
          <button
            @click="resetView"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                  bg-indigo-600 hover:bg-indigo-700 text-white font-medium
                  shadow transition-all duration-200"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kurslara Geri Dön
          </button>
        </div>

        <div v-if="loading" class="text-gray-500 dark:text-gray-400 italic">Yükleniyor...</div>

        <div v-else>
          <div v-if="lessons.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="lesson in lessons"
              :key="lesson.id"
              class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                    rounded-xl shadow hover:shadow-lg transition flex flex-col justify-between"
            >
              <div>
                <h2 class="text-lg font-semibold mb-1 text-gray-900 dark:text-gray-100">
                  {{ lesson.title }}
                </h2>

                <!-- İçerik -->
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-3">
                  {{ lesson.content || 'İçerik mevcut değil.' }}
                </p>

                <!-- Video URL -->
                <div v-if="lesson.video_url" class="mt-2">
                  <a
                    :href="lesson.video_url"
                    target="_blank"
                    class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 text-sm font-medium hover:underline"
                  >
                    🎥 Videoyu İzle
                  </a>
                </div>
              </div>
            </div>
          </div>

          <p v-else class="text-gray-500 dark:text-gray-400 text-center mt-10">
            Bu kursa ait ders bulunamadı.
          </p>
        </div>
      </template>

    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ref, watch, onMounted } from 'vue'
import { courseStore } from '@/Stores/courseStore'
import { paginationStore } from '@/Stores/paginationStore'
import Pagination from '@/Components/Pagination.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { inertiaDelete, inertiaGet } from '@/Helpers/inertiaActions'

/* Inertia Props */
const { props } = usePage()
const search = ref(props.filters?.search || '')

/* State'ler */
const showingLessons = ref(false)
const lessons = ref([])
const activeCourse = ref(null)
const loading = ref(false)

/* Kurslar */
onMounted(() => {
  courseStore.setCourses(props.courses?.data || [])
  paginationStore.init({
    links: props.courses?.links,
    url: route('admin.courses.index'),
    onDataUpdate: (data) => courseStore.setCourses(data),
  })
})

watch(
  () => props.courses,
  (val) => {
    if (!val) return
    courseStore.setCourses(val.data || [])
    paginationStore.setLinks(val.links || [])
  }
)

/* Arama */
watch(search, (value) => {
  inertiaGet(
    route('admin.courses.index'),
    { search: value },
    {
      preserveState: true,
      replace: true,
      onSuccess: (page) => {
        courseStore.setCourses(page.props.courses?.data || [])
        paginationStore.setLinks(page.props.courses?.links || [])
      },
    }
  )
})

/* Dersleri yükle */
const loadLessons = async (course) => {
  activeCourse.value = course
  showingLessons.value = true
  loading.value = true
  try {
    const res = await window.axios.get(`/admin/api/courses/${course.id}/lessons`)
    lessons.value = res.data
  } catch (err) {
    console.error(err)
    window.showToast('Dersler yüklenemedi ❌', 'error')
  } finally {
    loading.value = false
  }
}

/* Kurslara geri dön */
const resetView = () => {
  showingLessons.value = false
  activeCourse.value = null
  lessons.value = []
}

/* Silme */
const confirm = ref({ visible: false, title: '', message: '', course: null })

function confirmDelete(course) {
  confirm.value = {
    visible: true,
    title: `${course.title} kursunu silmek istiyor musun?`,
    message: 'Bu işlem geri alınamaz. Kurs ve bağlı dersler silinecek.',
    course,
  }
}

function performDelete() {
  if (!confirm.value.course) return
  inertiaDelete(route('admin.courses.destroy', { course: confirm.value.course.id }), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      courseStore.removeCourse(confirm.value.course.id)
      confirm.value.visible = false
      window.showToast('🗑️ Kurs başarıyla silindi!', 'success')
    },
  })
}
</script>

<style scoped>
.btn-primary {
  @apply px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition;
}
.btn-detail {
  @apply px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition;
}
.btn-edit {
  @apply px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded transition;
}
.btn-delete {
  @apply px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition;
}
</style>
