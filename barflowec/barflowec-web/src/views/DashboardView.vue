<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const dashboard = ref(null)
const loading = ref(true)
const error = ref('')

const colorClasses = {
  purple: 'bg-purple-600 text-white',
  green: 'bg-emerald-500 text-white',
  yellow: 'bg-amber-400 text-slate-950',
  red: 'bg-rose-500 text-white',
}

const metrics = computed(() => dashboard.value?.metrics || [])
const quotes = computed(() => dashboard.value?.recent_quotes || [])
const events = computed(() => dashboard.value?.upcoming_events || [])
const income = computed(() => dashboard.value?.income || { paid: 0, pending: 0 })

const formatMoney = (value) => `$${Number(value || 0).toFixed(2)}`

const statusClass = (status) => {
  return {
    aprobada: 'bg-emerald-50 text-emerald-700',
    pendiente: 'bg-amber-50 text-amber-700',
    enviada: 'bg-purple-50 text-purple-700',
    rechazada: 'bg-red-50 text-red-700',
  }[status] || 'bg-slate-100 text-slate-700'
}

onMounted(async () => {
  try {
    await auth.fetchUser()

    const { data } = await api.get('/dashboard')
    dashboard.value = data
  } catch (exception) {
    console.error(exception)
    error.value = 'No se pudo cargar el dashboard. Inicia sesión nuevamente.'
    localStorage.removeItem('barflowec_token')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section v-if="loading" class="rounded-2xl bg-white p-8 text-slate-500 shadow-sm">
    Cargando dashboard...
  </section>

  <section v-else-if="error" class="rounded-2xl bg-white p-8 shadow-sm">
    <h3 class="text-lg font-bold text-slate-950">Sesión expirada</h3>
    <p class="mt-2 text-sm text-slate-500">{{ error }}</p>
    <a
      href="/login"
      class="mt-5 inline-flex rounded-xl bg-purple-600 px-5 py-3 text-sm font-bold text-white hover:bg-purple-700"
    >
      Ir al login
    </a>
  </section>

  <section v-else class="space-y-6">
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="metric in metrics"
        :key="metric.label"
        class="rounded-2xl p-5 shadow-sm"
        :class="colorClasses[metric.color]"
      >
        <p class="text-sm opacity-80">{{ metric.label }}</p>

        <div class="mt-4 flex items-end justify-between">
          <strong class="text-3xl font-bold">{{ metric.value }}</strong>
          <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold">
            {{ metric.change }}
          </span>
        </div>
      </article>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
      <article class="rounded-2xl bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase text-slate-400">Ingresos cobrados</p>
        <p class="mt-2 text-3xl font-bold text-emerald-600">{{ formatMoney(income.paid) }}</p>
      </article>

      <article class="rounded-2xl bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase text-slate-400">Ingresos pendientes</p>
        <p class="mt-2 text-3xl font-bold text-rose-600">{{ formatMoney(income.pending) }}</p>
      </article>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
      <article class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
          <h3 class="text-lg font-bold text-slate-950">Cotizaciones recientes</h3>
          <span class="text-sm text-slate-400">Datos reales</span>
        </div>

        <div v-if="quotes.length === 0" class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-slate-500">
          No hay cotizaciones registradas.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-slate-100 text-slate-500">
                <th class="py-3 font-semibold">Código</th>
                <th class="py-3 font-semibold">Cliente</th>
                <th class="py-3 font-semibold">Tipo</th>
                <th class="py-3 font-semibold">Valor</th>
                <th class="py-3 font-semibold">Estado</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="quote in quotes" :key="quote.id" class="border-b border-slate-50">
                <td class="py-4 font-semibold text-slate-900">{{ quote.code || `#${quote.id}` }}</td>
                <td class="py-4 text-slate-600">{{ quote.client }}</td>
                <td class="py-4 text-slate-600">{{ quote.event_type }}</td>
                <td class="py-4 font-semibold text-slate-900">{{ formatMoney(quote.amount) }}</td>
                <td class="py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(quote.status)">
                    {{ quote.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <article class="rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="mb-5 text-lg font-bold text-slate-950">Próximos eventos</h3>

        <div v-if="events.length === 0" class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-slate-500">
          No hay eventos próximos.
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="event in events"
            :key="`${event.name}-${event.date}`"
            class="rounded-xl border border-slate-100 p-4"
          >
            <p class="font-semibold text-slate-950">{{ event.name }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ event.date }} - {{ event.location }}</p>
            <p class="mt-3 text-xs font-semibold uppercase text-purple-600">
              {{ event.bartender }}
            </p>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>
