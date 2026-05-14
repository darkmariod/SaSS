import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

const app = createApp(App)

const pinia = createPinia()
app.use(pinia)
app.use(router)

// Inicializar auth (cargar usuario desde cookie de sesión)
// antes de montar la app para que el router guard funcione correctamente.
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
auth.init().finally(() => {
  app.mount('#app')
})
