<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('admin@barflowec.com')
const password = ref('password')
const error = ref('')

const submit = async () => {
  error.value = ''

  try {
    await auth.login({
      email: email.value,
      password: password.value,
    })

    router.push({ name: 'dashboard' })
  } catch {
    error.value = 'Credenciales inválidas o API no disponible.'
  }
}
</script>

<template>
  <main class="grid min-h-screen place-items-center bg-slate-100 px-4">
    <section class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl">
      <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-wide text-purple-600">BarFlowEC</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-950">Iniciar sesión</h1>
        <p class="mt-2 text-sm text-slate-500">Panel administrativo comercial</p>
      </div>

      <form class="space-y-5" @submit.prevent="submit">
        <div>
          <label class="text-sm font-medium text-slate-700">Email</label>
          <input
            v-model="email"
            type="email"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500 focus:ring-4 focus:ring-purple-100"
          />
        </div>

        <div>
          <label class="text-sm font-medium text-slate-700">Password</label>
          <input
            v-model="password"
            type="password"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500 focus:ring-4 focus:ring-purple-100"
          />
        </div>

        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">
          {{ error }}
        </p>

        <button
          type="submit"
          class="w-full rounded-xl bg-purple-600 px-4 py-3 font-semibold text-white shadow-lg shadow-purple-200 hover:bg-purple-700"
        >
          Entrar
        </button>
      </form>
    </section>
  </main>
</template>
