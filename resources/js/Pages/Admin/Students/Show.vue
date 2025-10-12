<template>
  <AdminLayout>
    <div class="max-w-6xl mx-auto py-10 px-6 space-y-8">
      <!--  Öğrenci Bilgileri -->
      <div class="flex justify-between items-start flex-wrap gap-3">
        <div>
          <h1 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
            {{ student.name }}
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">📧 {{ student.email }}</p>
        </div>

        <Link
          :href="route('admin.students.index')"
          class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm"
        >
          ← Geri
        </Link>
      </div>

      <!--  Mini İstatistikler -->
      <div class="grid sm:grid-cols-3 gap-6">
        <div
          v-for="(stat, key) in stats"
          :key="key"
          class="p-5 bg-white/70 dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700 
                 rounded-2xl shadow hover:shadow-lg transition transform hover:scale-[1.02]"
        >
          <h3 class="text-sm text-gray-500 dark:text-gray-400 mb-1 capitalize">
            {{ stat.label }}
          </h3>
          <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
            {{ stat.value }}
          </p>
        </div>
      </div>

      <!--  Kayıtlı Kurslar -->
      <div>
        <h2
          class="text-2xl font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center gap-2"
        >
          📚 Kayıtlı Kurslar
        </h2>

        <div v-if="student.courses.length" class="space-y-8">
          <div
            v-for="course in student.courses"
            :key="course.id"
            class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                   rounded-xl shadow-md hover:shadow-lg transition"
          >
            <!-- Kurs Bilgisi -->
            <div class="flex justify-between items-center mb-3">
              <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                  {{ course.title }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  👨‍🏫 Eğitmen: {{ course.instructor }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                  📅 Başlangıç: {{ formatDate(course.start_date) || 'Bilinmiyor' }}
                </p>
              </div>

              <Link
                :href="route('admin.courses.show', course)"
                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg"
              >
                Kursu Gör
              </Link>
            </div>

            <!-- Dersler -->
            <div v-if="course.lessons && course.lessons.length">
              <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-300 mb-2">
                📘 Dersler ({{ course.lessons.length }})
              </h4>
              <ul class="space-y-2">
                <li
                  v-for="lesson in course.lessons"
                  :key="lesson.id"
                  class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 
                         flex justify-between items-center text-sm"
                >
                  <span class="text-gray-800 dark:text-gray-200 font-medium">
                    {{ lesson.title }}
                  </span>

                  <a
                    v-if="lesson.video_url"
                    :href="lesson.video_url"
                    target="_blank"
                    class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                  >
                    🎥 İzle
                  </a>
                </li>
              </ul>
            </div>

            <p
              v-else
              class="text-gray-500 dark:text-gray-400 text-sm italic mt-2"
            >
              Henüz ders eklenmemiş.
            </p>
          </div>
        </div>

        <!--  Hiç kurs yok -->
        <p
          v-else
          class="text-gray-500 dark:text-gray-400 italic text-center mt-10"
        >
          Bu öğrenci henüz herhangi bir kursa kayıtlı değil.
        </p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

/**
 *  Props verileri
 */
const { props } = usePage()
const student = props.student

/**
 *  Mini İstatistikler
 */
const stats = computed(() => {
  const totalCourses = student.courses?.length || 0
  const totalLessons = student.courses?.reduce(
    (sum, c) => sum + (c.lessons?.length || 0),
    0
  )
  const totalVideos = student.courses?.reduce(
    (sum, c) => sum + (c.lessons?.filter(l => !!l.video_url)?.length || 0),
    0
  )

  return {
    totalCourses: { label: 'Toplam Kurs', value: totalCourses },
    totalLessons: { label: 'Toplam Ders', value: totalLessons },
    totalVideos: { label: 'Video İçerikleri', value: totalVideos },
  }
})

/**
 *  Tarihi biçimlendir
 */
function formatDate(dateStr) {
  if (!dateStr) return null
  return new Date(dateStr).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<style scoped>
a {
  @apply transition;
}
</style>
