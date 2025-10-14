<template>
  <StudentLayout>
    <!-- Kurstan çıkma onayı -->
    <ConfirmModal
      v-if="confirm.visible"
      :title="confirm.title"
      :message="confirm.message"
      :visible="confirm.visible"
      @confirm="performUnenroll"
      @cancel="confirm.visible = false"
    />

    <!-- Toast -->
    <transition name="fade">
      <div
        v-if="studentStore.toast.visible"
        :class="[
          'fixed bottom-6 right-6 px-4 py-2.5 rounded-lg shadow-lg text-white font-medium transition',
          studentStore.toast.type === 'success'
            ? 'bg-green-600'
            : studentStore.toast.type === 'error'
            ? 'bg-red-600'
            : 'bg-blue-600',
        ]"
      >
        {{ studentStore.toast.message }}
      </div>
    </transition>

    <div class="max-w-7xl mx-auto py-10 px-6">
      <!-- Başlık ve Arama -->
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
          🎓 Tüm Kurslar
        </h1>

        <input
          v-model="search"
          type="text"
          placeholder="Kurs veya eğitmen ara..."
          class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                 focus:ring focus:ring-indigo-400 outline-none w-64"
        />
      </div>

      <!-- Kurs Kartları -->
      <div v-if="studentStore.courses.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="course in studentStore.courses"
          :key="course.id"
          class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                 rounded-xl shadow hover:shadow-lg transition"
        >
          <h2 class="text-xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
            {{ course.title }}
          </h2>

          <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">
            {{ course.description || 'Açıklama bulunamadı.' }}
          </p>

          <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
            👨‍🏫 {{ course.instructor }}
          </p>

          <div class="flex justify-between items-center">
            <Link
              :href="route('student.courses.show', course.id)"
              class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium"
            >
              Detay
            </Link>

            <!-- Kursa Katıl / Kayıtlısın + Kurstan Çık -->
            <template v-if="studentStore.isEnrolled(course.id)">
              <div class="flex items-center gap-2">
                <span class="text-green-600 dark:text-green-400 text-xs font-semibold">
                  ✅ Kayıtlısın
                </span>
                <button
                  @click="confirmUnenroll(course)"
                  class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition"
                >
                  ❌ Kurstan Çık
                </button>
              </div>
            </template>

            <template v-else>
              <button
                @click="enroll(course.id)"
                class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg transition"
              >
                ✅ Katıl
              </button>
            </template>
          </div>
        </div>
      </div>

      <p v-else class="text-gray-500 dark:text-gray-400 text-center mt-10">
        Hiç kurs bulunamadı.
      </p>

      <!-- Sayfalama -->
      <Pagination />
    </div>
  </StudentLayout>
</template>

<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ref, watch, onMounted } from 'vue'
import { watchDebounced } from '@vueuse/core'
import { inertiaGet, inertiaPost, inertiaDelete } from '@/Helpers/inertiaActions'
import { studentStore } from '@/Stores/studentStore'
import { paginationStore } from '@/Stores/paginationStore'
import Pagination from '@/Components/Pagination.vue'

const page = usePage()
const search = ref(page.props.filters?.search || '')

/* ------------------------------
 * Modal state
------------------------------ */
const confirm = ref({
  visible: false,
  title: '',
  message: '',
  id: null,
})

/* ------------------------------
 *  İlk yükleme
------------------------------ */
onMounted(() => {
  studentStore.setCourses(page.props.courses?.data || [])
  studentStore.setEnrolled(page.props.enrolledCourseIds || [])

  paginationStore.init({
    links: page.props.courses?.links || [],
    url: route('student.courses.index'),
    onDataUpdate: (data) => studentStore.setCourses(data),
  })
})

watch(() => page.props.courses, (val) => {
  if (!val) return
  studentStore.setCourses(val.data || [])
  paginationStore.setLinks(val.links || [])
})

// Arama (backend senkronize, debounced)
watchDebounced(
  search,
  (val) => {
    const term = (val ?? '').trim()
    inertiaGet(
      route('student.courses.index'),
      { search: term || undefined, page: 1 },
      {
        preserveState: true,
        replace: true,
        only: ['courses', 'filters', 'enrolledCourseIds'],
        onSuccess: (page) => {
          studentStore.setCourses(page.props.courses?.data || [])
          paginationStore.setLinks(page.props.courses?.links || [])
          studentStore.setEnrolled(page.props.enrolledCourseIds || [])
        },
      }
    )
  },
  { debounce: 300, maxWait: 800 }
)

/* ------------------------------
 *  Kursa Katıl
------------------------------ */
function enroll(id) {
  inertiaPost(route('student.courses.enroll', id), {}, {
    onSuccess: () => {
      studentStore.showToast('🎉 Kursa başarıyla katıldın!', 'success')
      studentStore.enrolledIds.push(id)
    },
    onError: () => studentStore.showToast('❌ Kayıt sırasında bir hata oluştu.', 'error'),
  })
}

/* ------------------------------
 *  Kurstan Çık Modalını Aç
------------------------------ */
function confirmUnenroll(course) {
  confirm.value = {
    visible: true,
    title: `${course.title} kursundan çıkmak istiyor musun?`,
    message: 'Bu işlemden sonra kursa ve derslerine erişimin kaldırılacaktır.',
    id: course.id,
  }
}

/* ------------------------------
 *  Kurstan Çık İşlemi (Modal Onayı)
------------------------------ */
function performUnenroll() {
  inertiaDelete(route('student.courses.unenroll', confirm.value.id), {
    onSuccess: () => {
      studentStore.showToast('❌ Kurstan başarıyla çıkıldı.', 'success')
      studentStore.enrolledIds = studentStore.enrolledIds.filter(
        (id) => id !== confirm.value.id
      )
      confirm.value.visible = false
    },
    onError: () => studentStore.showToast('⚠️ İşlem başarısız oldu.', 'error'),
  })
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
