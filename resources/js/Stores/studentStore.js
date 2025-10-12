import { reactive } from 'vue'

export const studentStore = reactive({
  // Admin tarafı için
  students: [],

  // Öğrenci tarafı (mevcut kalsın)
  courses: [],
  enrolledIds: [],
  toast: {
    visible: false,
    message: '',
    type: 'success',
  },

  /* -----------------------------------------
   * ADMIN TARAFI — Öğrenciler
  ----------------------------------------- */
  setStudents(list) {
    this.students = list || []
  },

  removeStudent(id) {
    this.students = this.students.filter(s => s.id !== id)
  },

  /* -----------------------------------------
   * STUDENT TARAFI — Kurslar
  ----------------------------------------- */
  setCourses(list) {
    this.courses = list || []
  },

  setEnrolled(ids) {
    this.enrolledIds = ids || []
  },

  isEnrolled(courseId) {
    return this.enrolledIds.includes(courseId)
  },

  /* -----------------------------------------
   * Genel Toast Mesajı
  ----------------------------------------- */
  showToast(message, type = 'success') {
    this.toast.message = message
    this.toast.type = type
    this.toast.visible = true
    setTimeout(() => (this.toast.visible = false), 3000)
  },
})
