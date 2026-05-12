import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: true,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.user),
  },

  actions: {
    /**
     * Inicializa el estado de auth al cargar la app.
     * Intenta obtener el usuario vía cookie de sesión (httpOnly).
     * Si la sesión expiró, el usuario queda como null (login requerido).
     */
    async init() {
      this.loading = true

      try {
        await this.fetchUser()
      } catch {
        this.user = null
      } finally {
        this.loading = false
      }
    },

    /**
     * Obtiene el usuario autenticado desde GET /api/me.
     * La cookie de sesión se envía automáticamente gracias a withCredentials.
     */
    async fetchUser() {
      const { data } = await api.get('/me')

      this.user = data.user

      return data.user
    },

    /**
     * Inicia sesión con cookies httpOnly.
     *
     * Flujo:
     * 1. GET /sanctum/csrf-cookie — obtiene XSRF-TOKEN
     * 2. POST /api/login — envía credenciales + X-XSRF-TOKEN
     * 3. Backend setea cookie de sesión httpOnly
     * 4. Obtenemos el usuario
     */
    async login(credentials) {
      this.loading = true

      try {
        // Inicializar CSRF token antes del login
        // Usamos URL absoluta para evitar el prefijo /api
        await api.get('http://127.0.0.1:8000/sanctum/csrf-cookie')

        const { data } = await api.post('/login', credentials)

        this.user = data.user

        return data
      } finally {
        this.loading = false
      }
    },

    /**
     * Cierra sesión: invalida la sesión en el backend.
     * La cookie httpOnly se elimina automáticamente.
     */
    async logout() {
      try {
        await api.post('/logout')
      } catch {
        // Si la sesión ya expiró, ignoramos el error
      } finally {
        this.user = null
      }
    },
  },
})
