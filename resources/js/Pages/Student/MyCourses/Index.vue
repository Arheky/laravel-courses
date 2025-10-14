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
          📚 Kurslarım
        </h1>

        <!-- Arama Kutusu -->
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
      <div v-if="courses.data?.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="course in courses.data"
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
              :href="route('student.mycourses.show', course.id)"
              class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium"
            >
              Dersleri Gör
            </Link>

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
          </div>
        </div>
      </div>

      <p v-else class="text-gray-500 dark:text-gray-400 text-center mt-10">
        Henüz kayıtlı olduğun bir kurs bulunamadı.
      </p>
    </div>
  </StudentLayout>
</template>

<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { ref, watch, onMounted } from 'vue'
import { inertiaDelete } from '@/Helpers/inertiaActions'
import { studentStore } from '@/Stores/studentStore'

/* ------------------------------
 *  Props
------------------------------ */
const page = usePage()
const courses = ref(page.props.courses || { data: [] })
const search = ref(page.props.filters?.search || '')

/* ------------------------------
 * Modal State
------------------------------ */
const confirm = ref({
  visible: false,
  title: '',
  message: '',
  id: null,
})

/* ------------------------------
 * Sayfa Yüklendiğinde Kursları Güncelle
------------------------------ */
onMounted(() => {
  studentStore.setCourses(courses.value.data || [])
  studentStore.setEnrolled((courses.value.data || []).map(c => c.id))
})

/* ------------------------------
 * Arama Özelliği (Courses/Index.vue ile aynı)
------------------------------ */
watchDebounced(
  search,
  (val) => {
    const term = (val ?? '').trim()
    inertiaGet(
      route('student.mycourses.index'),
      { search: term || undefined, page: 1 },
      {
        preserveState: true,
        replace: true,
        only: ['courses', 'filters'],
        onSuccess: (page) => {
          studentStore.setCourses(page.props.courses?.data || [])
          paginationStore.setLinks(page.props.courses?.links || [])
        },
      }
    )
  },
  { debounce: 300, maxWait: 800 }
)

/* ------------------------------
 * Kurstan Çık Modalını Aç
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
 * Kurstan Çık İşlemi
------------------------------ */
function performUnenroll() {
  inertiaDelete(route('student.courses.unenroll', confirm.value.id), {
    onSuccess: () => {
      studentStore.showToast('❌ Kurstan başarıyla çıkıldı.', 'success')
      studentStore.enrolledIds = studentStore.enrolledIds.filter(
        (id) => id !== confirm.value.id
      )
      courses.value.data = courses.value.data.filter(
        (course) => course.id !== confirm.value.id
      )
      confirm.value.visible = false
    },
    onError: () => studentStore.showToast('⚠️ İşlem başarısız oldu.', 'error'),
  })
}
</script>
