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
      <!--  Başlık + Arama -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
          🎓 Öğrenciler
        </h1>

        <input
          v-model="search"
          type="text"
          placeholder="Öğrenci veya e-posta ara..."
          class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                 focus:ring focus:ring-indigo-400 outline-none w-64"
        />
      </div>

      <!--  Öğrenci Kartları -->
      <div v-if="studentStore.students.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="student in studentStore.students"
          :key="student.id"
          class="p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                 rounded-xl shadow hover:shadow-lg transition"
        >
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
            {{ student.name }}
          </h2>

          <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
            📧 {{ student.email }}
          </p>

          <div class="flex justify-between items-center">
            <Link
              :href="route('admin.students.show', student)"
              class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm font-medium"
            >
              📘 Kursları Gör
            </Link>

            <button @click="confirmDelete(student)" class="btn-delete">
              Sil
            </button>
          </div>
        </div>
      </div>

      <!--  Boş Liste -->
      <p v-else class="text-center text-gray-500 dark:text-gray-400 mt-10">
        Henüz öğrenci bulunamadı.
      </p>

      <!--  Sayfalama -->
      <Pagination />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref, watch, onMounted } from 'vue'
import { watchDebounced } from '@vueuse/core'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import Pagination from '@/Components/Pagination.vue'
import { studentStore } from '@/Stores/studentStore'
import { paginationStore } from '@/Stores/paginationStore'
import { inertiaDelete, inertiaGet } from '@/Helpers/inertiaActions'

/**
 *  Props verileri
 */
const { props } = usePage()
const search = ref(props.filters?.search || '')

/**
 *  Silme onayı
 */
const confirm = ref({
  visible: false,
  title: '',
  message: '',
  student: null,
})

/**
 *  Silme modalını aç
 */
function confirmDelete(student) {
  confirm.value = {
    visible: true,
    title: `${student.name} adlı öğrenciyi silmek istiyor musun?`,
    message: 'Bu işlem geri alınamaz. Öğrenciye ait tüm kurs kayıtları kaldırılacaktır.',
    student, 
  }
}

/**
 *  Silme işlemi
 */
function performDelete() {
  inertiaDelete(route('admin.students.destroy', confirm.value.student), {
    preserveScroll: true,
    onSuccess: () => {
      studentStore.removeStudent(confirm.value.student.id)
      confirm.value.visible = false

      window.dispatchEvent(
        new CustomEvent('show-toast', {
          detail: { message: '🗑️ Öğrenci başarıyla silindi!', type: 'success' },
        })
      )
    },
  })
}

/**
 *  Sayfa yüklendiğinde store başlat
 */
onMounted(() => {
  studentStore.setStudents(props.students?.data || [])
  paginationStore.init({
    links: props.students?.links,
    url: route('admin.students.index'),
    onDataUpdate: (data) => studentStore.setStudents(data),
  })
})

/**
 *  Arama güvenli şekilde
 */
watchDebounced(
  search,
  (val) => {
    const term = (val ?? '').trim()
    inertiaGet(
      route('admin.students.index'),
      { search: term || undefined },
      {
        preserveState: true,
        replace: true,
        only: ['students', 'filters'],
        onSuccess: (page) => {
          studentStore.setStudents(page.props.students?.data || [])
          paginationStore.setLinks(page.props.students?.links || [])
        },
      }
    )
  },
  { debounce: 300, maxWait: 800 }
</script>

<style scoped>
.btn-delete {
  @apply px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition;
}
</style>
