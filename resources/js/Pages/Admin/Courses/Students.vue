<template>
  <AdminLayout>
    <!-- Silme onayı modal -->
    <ConfirmModal
      v-if="confirm.visible"
      :title="confirm.title"
      :message="confirm.message"
      :visible="confirm.visible"
      @confirm="performRemove"
      @cancel="confirm.visible = false"
    />

    <div class="max-w-6xl mx-auto py-10 px-6 space-y-6">
      <!-- Başlık + Arama + Geri -->
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
          👨‍🎓 {{ course.title }} — Kayıtlı Öğrenciler
        </h1>

        <div class="flex items-center gap-3">
          <!-- Arama -->
          <input
            v-model="filters.search"
            type="text"
            placeholder="Öğrenci adı veya e-posta ara..."
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                   focus:ring focus:ring-indigo-400 outline-none w-64"
          />

          <!-- Geri -->
          <Link
            :href="route('admin.courses.show', { course: course.id })"
            class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm"
          >
            ← Geri
          </Link>
        </div>
      </div>

      <!-- Öğrenci Kartları -->
      <transition-group name="fade" tag="div" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="student in students.data"
          :key="student.id"
          class="p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow hover:shadow-lg transition flex flex-col justify-between"
        >
          <div>
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
              {{ student.name }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
              📧 {{ student.email }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Kayıt Tarihi: {{ formatDate(student.pivot?.created_at) || 'Bilinmiyor' }}
            </p>
          </div>

          <button
            @click="confirmRemove(student)"
            class="mt-4 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition"
          >
            Kurstan Çıkar
          </button>
        </div>
      </transition-group>

      <!-- Boş Liste -->
      <p v-if="!students.data.length" class="text-gray-500 dark:text-gray-400 text-center mt-10">
        Henüz kayıtlı öğrenci bulunamadı.
      </p>

      <!-- Sayfalama -->
      <Pagination
        :links="students.links"
        @navigate="goToPage"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { inertiaDelete } from '@/Helpers/inertiaActions'

const { props } = usePage()
const course = props.course
const filters = ref(props.filters || {})
const students = ref(props.students || { data: [], links: [] })

const confirm = ref({
  visible: false,
  title: '',
  message: '',
  id: null,
})

// Tarih formatlayıcı
function formatDate(dateStr) {
  if (!dateStr) return null
  return new Date(dateStr).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// Onay penceresini aç
function confirmRemove(student) {
  confirm.value = {
    visible: true,
    title: `${student.name} öğrencisini kurstan çıkarmak istiyor musun?`,
    message: 'Bu işlem geri alınamaz. Öğrenci bu kurstan silinecektir.',
    id: student.id,
  }
}

// Öğrenciyi kurstan çıkar
function performRemove() {
  inertiaDelete(route('admin.courses.students.remove', { course: course.id, user: confirm.value.id }), {
    preserveScroll: true,
    onSuccess: () => {
      students.value.data = students.value.data.filter((s) => s.id !== confirm.value.id)
      confirm.value.visible = false
    },
  })
}

// Arama
watch(
  () => filters.value.search,
  (value) => {
    router.get(
      route('admin.courses.students', { course: course.id }),
      { search: value },
      { preserveState: true, replace: true, preserveScroll: true }
    )
  }
)


// Sayfalama yönlendirme
function goToPage(url) {
  if (!url) return
  router.visit(url, { preserveState: true, preserveScroll: true })
}
</script>

<style scoped>
button {
  @apply transition;
}

/* Fade animasyonu */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: scale(0.97);
}
</style>
