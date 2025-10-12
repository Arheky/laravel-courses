<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-6 py-10">
      <!-- Başlık -->
      <h1 class="text-3xl font-bold mb-10 flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
        🧭 Dashboard
      </h1>

      <!-- İstatistik Kartları -->
      <div class="grid md:grid-cols-3 lg:grid-cols-6 sm:grid-cols-2 gap-6 mb-12">
        <div
          v-for="(value, key) in stats"
          :key="key"
          class="p-6 bg-gradient-to-br from-indigo-500/10 to-indigo-600/5 dark:from-indigo-500/20 dark:to-indigo-700/10
                 border border-gray-200 dark:border-gray-700 rounded-2xl shadow hover:shadow-lg transition transform hover:scale-[1.02]"
        >
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-1 capitalize">
            {{ labels[key] }}
          </p>
          <p class="text-4xl font-bold text-gray-900 dark:text-gray-100">
            {{ value }}
          </p>
        </div>
      </div>

      <!-- Son Kayıtlar -->
      <div class="p-6 bg-white/80 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-lg mb-12">
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
          🧾 Son Kayıtlar
        </h2>

        <div v-if="latestEnrollments.length" class="overflow-x-auto rounded-lg">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                <th class="px-4 py-3 text-left">👤 Öğrenci</th>
                <th class="px-4 py-3 text-left">📘 Kurs</th>
                <th class="px-4 py-3 text-left">📅 Kayıt Tarihi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="record in latestEnrollments"
                :key="record.id"
                class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
              >
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                  {{ record.user_name }}
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ record.user_email }}</p>
                </td>
                <td class="px-4 py-3">{{ record.course_title }}</td>
                <td class="px-4 py-3">{{ record.enrolled_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-else class="text-gray-500 dark:text-gray-400 text-center py-6">
          Henüz kayıt bulunamadı.
        </p>
      </div>

      <!-- Popüler Kurslar -->
      <div class="p-6 bg-white/80 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-lg mb-12">
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
          🔥 Popüler Kurslar
        </h2>

        <div v-if="popularCourses.length" class="space-y-3">
          <div
            v-for="course in popularCourses"
            :key="course.id"
            class="flex justify-between items-center p-5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:shadow-md transition"
          >
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                {{ course.title }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                👥 Katılım: <strong>{{ course.enrollments }}</strong>
              </p>
            </div>
            <Link
              :href="route('admin.courses.edit', course.id)"
              class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm"
            >
              Düzenle
            </Link>
          </div>
        </div>

        <p v-else class="text-gray-500 dark:text-gray-400 text-center py-6">
          Henüz popüler kurs bulunamadı.
        </p>
      </div>

      <!-- Son Eklenen Kurslar -->
      <div class="p-6 bg-white/80 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
          🆕 Son Eklenen Kurslar
        </h2>

        <div v-if="latestCourses.length" class="overflow-x-auto rounded-lg">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                <th class="px-4 py-3 text-left">📘 Kurs Başlığı</th>
                <th class="px-4 py-3 text-left">👨‍🏫 Eğitmen</th>
                <th class="px-4 py-3 text-left">📅 Oluşturulma Tarihi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="course in latestCourses"
                :key="course.id"
                class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
              >
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                  {{ course.title }}
                </td>
                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                  {{ course.instructor }}
                </td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                  {{ course.created_at }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-else class="text-gray-500 dark:text-gray-400 text-center py-6">
          Henüz yeni kurs eklenmemiş.
        </p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'

// Backend’ten gelen Inertia props
const { props } = usePage()

//  İstatistikler
const stats = props.stats || {
  totalCourses: 0,
  totalStudents: 0,
  totalEnrollments: 0,
  todayEnrollments: 0,
  latestLessons: 0,
  latestStudents: 0,
}

// Vue tarafında gösterilecek başlıklar
const labels = {
  totalCourses: 'Toplam Kurs',
  totalStudents: 'Toplam Öğrenci',
  totalEnrollments: 'Toplam Kayıt',
  todayEnrollments: 'Bugünkü Kayıtlar',
  latestLessons: 'Son 7 Günde Eklenen Ders',
  latestStudents: 'Son 7 Günde Yeni Öğrenci',
}

// Props verileri
const latestEnrollments = props.latestEnrollments || []
const popularCourses = props.popularCourses || []
const latestCourses = props.latestCourses || []
</script>

<style scoped>
table th,
table td {
  @apply text-sm;
}
</style>
