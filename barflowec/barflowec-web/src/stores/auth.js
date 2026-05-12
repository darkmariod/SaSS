import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('barflowec_token'),
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },

  actions: {
    async login(credentials) {
      this.loading = true

      try {
        const { data } = await api.post('/login', credentials)

        this.token = data.token
        this.user = data.user

        localStorage.setItem('barflowec_token', data.token)

        return data
      } finally {
        this.loading = false
      }
    },

    async fetchUser() {
      if (!this.token) return null

      const { data } = await api.get('/me')
      this.user = data.user

      return data.user
    },

    async logout() {
      try {
        if (this.token) {
          await api.post('/logout')
        }
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('barflowec_token')
      }
    },
  },
})
