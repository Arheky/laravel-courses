import { reactive } from 'vue'

export const courseStore = reactive({
  courses: [],
  pagination: [],

  setCourses(data) {
    this.courses = data
  },
  setPagination(data) {
    this.pagination = data
  },
  removeCourse(id) {
    this.courses = this.courses.filter((c) => c.id !== id)
  },
})
