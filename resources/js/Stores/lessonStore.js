import { reactive } from 'vue'

export const lessonStore = reactive({
  lessons: [],
  courses: [],
  pagination: [],

  setLessons(data) {
    this.lessons = data
  },
  setCourses(data) {
    this.courses = data
  },
  setPagination(data) {
    this.pagination = data
  },
  removeLesson(id) {
    this.lessons = this.lessons.filter((l) => l.id !== id)
  },
})
