import { ref } from 'vue'

export const userStore = {
  user: ref({}),

  setUser(data) {
    this.user.value = data
  },

  updateName(name) {
    this.user.value.name = name
  },
}
