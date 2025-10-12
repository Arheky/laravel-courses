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

    <div class="max-w-5xl mx-auto py-10 px-6 space-y-6">
      <!-- Başlık ve Geri Butonu -->
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
          {{ course.title }}
        </h1>

        <Link
          :href="route('student.mycourses.index')"
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

        <!-- Kurstan Çık / Kursa Katıl butonları -->
        <div class="mt-4 flex gap-3">
          <button
            v-if="enrolled"
            @click="confirmUnenroll"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm"
          >
            ❌ Kurstan Çık
          </button>
          <button
            v-else
            @click="enroll"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm"
          >
            ✅ Kursa Katıl
          </button>
        </div>
      </div>

      <!-- Ders Listesi -->
      <div>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">📚 Dersler</h2>

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
          </div>
        </div>

        <p v-else class="text-gray-500 dark:text-gray-400 text-center mt-6">
          Henüz ders bulunamadı.
        </p>
      </div>
    </div>
  </StudentLayout>
</template>

<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { inertiaPost, inertiaDelete } from '@/Helpers/inertiaActions'
import { studentStore } from '@/Stores/studentStore'

const { props } = usePage()
const course = props.course
const lessons = ref(props.course.lessons || [])
const enrolled = ref(props.enrolled || true) // Backend'den gelen durum

// Kurstan çıkma modalı
const confirm = ref({
  visible: false,
  title: '',
  message: '',
})

/* ------------------------------
 * Kursa Katıl
------------------------------ */
function enroll() {
  inertiaPost(route('student.courses.enroll', course.id), {}, {
    onSuccess: () => {
      enrolled.value = true
      studentStore.showToast('🎉 Kursa başarıyla katıldın!', 'success')
    },
    onError: () => studentStore.showToast('❌ Kursa katılım başarısız oldu.', 'error'),
  })
}

/* ------------------------------
 * Kurstan Çık Modalını Aç
------------------------------ */
function confirmUnenroll() {
  confirm.value = {
    visible: true,
    title: `${course.title} kursundan çıkmak istiyor musun?`,
    message: 'Bu işlemden sonra kursa ve derslerine erişimin kaldırılacaktır.',
  }
}

/* ------------------------------
 * Kurstan Çık İşlemi (Modal Onayı)
------------------------------ */
function performUnenroll() {
  inertiaDelete(route('student.courses.unenroll', course.id), {
    onSuccess: () => {
      enrolled.value = false
      confirm.value.visible = false
      studentStore.showToast('❌ Kurstan başarıyla çıkıldı.', 'success')

      // Kurslarım sayfasına yönlendirme
      router.visit(route('student.mycourses.index'))
    },
    onError: () => studentStore.showToast('⚠️ Kurstan çıkma işlemi başarısız oldu.', 'error'),
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

button {
  @apply transition;
}
</style>
