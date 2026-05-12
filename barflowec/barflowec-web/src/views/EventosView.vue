<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/services/api'

const eventos = ref([])
const clientes = ref([])
const cotizaciones = ref([])
const loading = ref(true)
const saving = ref(false)
const editingId = ref(null)
const error = ref('')

const form = reactive({
  cliente_id: '',
  cotizacion_id: '',
  name: '',
  event_date: '',
  location: '',
  bartender_name: '',
  status: 'programado',
  notes: '',
})

const isEditing = computed(() => Boolean(editingId.value))

const statusClasses = {
  programado: 'bg-amber-50 text-amber-700',
  confirmado: 'bg-emerald-50 text-emerald-700',
  en_proceso: 'bg-purple-50 text-purple-700',
  completado: 'bg-slate-100 text-slate-700',
  cancelado: 'bg-red-50 text-red-700',
}

const resetForm = () => {
  editingId.value = null
  form.cliente_id = ''
  form.cotizacion_id = ''
  form.name = ''
  form.event_date = ''
  form.location = ''
  form.bartender_name = ''
  form.status = 'programado'
  form.notes = ''
}

const fetchClientes = async () => {
  const { data } = await api.get('/clientes')
  clientes.value = data.data || data
}

const fetchCotizaciones = async () => {
  const { data } = await api.get('/cotizaciones')
  cotizaciones.value = data.data || data
}

const fetchEventos = async () => {
  const { data } = await api.get('/eventos')
  eventos.value = data.data || data
}

const loadData = async () => {
  loading.value = true
  await Promise.all([fetchClientes(), fetchCotizaciones(), fetchEventos()])
  loading.value = false
}

const submit = async () => {
  saving.value = true
  error.value = ''

  try {
    const payload = {
      ...form,
      cliente_id: Number(form.cliente_id),
      cotizacion_id: form.cotizacion_id ? Number(form.cotizacion_id) : null,
    }

    if (isEditing.value) {
      await api.put(`/eventos/${editingId.value}`, payload)
    } else {
      await api.post('/eventos', payload)
    }

    resetForm()
    await fetchEventos()
  } catch (exception) {
    error.value = exception.response?.data?.message || 'No se pudo guardar el evento.'
  } finally {
    saving.value = false
  }
}

const editEvento = (evento) => {
  editingId.value = evento.id
  form.cliente_id = evento.cliente_id || ''
  form.cotizacion_id = evento.cotizacion_id || ''
  form.name = evento.name || ''
  form.event_date = evento.event_date || ''
  form.location = evento.location || ''
  form.bartender_name = evento.bartender_name || ''
  form.status = evento.status || 'programado'
  form.notes = evento.notes || ''
}

const deleteEvento = async (evento) => {
  if (!confirm(`Eliminar evento ${evento.name}?`)) return

  await api.delete(`/eventos/${evento.id}`)
  await fetchEventos()
}

onMounted(loadData)
</script>

<template>
  <section class="grid gap-6 xl:grid-cols-[430px_1fr]">
    <form class="rounded-2xl bg-white p-6 shadow-sm" @submit.prevent="submit">
      <div class="mb-5">
        <h3 class="text-lg font-bold text-slate-950">
          {{ isEditing ? 'Editar evento' : 'Nuevo evento' }}
        </h3>
        <p class="text-sm text-slate-500">Programa el servicio operativo.</p>
      </div>

      <div class="space-y-4">
        <div>
          <label class="text-sm font-semibold text-slate-700">Cliente</label>
          <select
            v-model="form.cliente_id"
            required
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          >
            <option value="">Seleccionar cliente</option>
            <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
              {{ cliente.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Cotización</label>
          <select
            v-model="form.cotizacion_id"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          >
            <option value="">Sin cotización</option>
            <option v-for="cotizacion in cotizaciones" :key="cotizacion.id" :value="cotizacion.id">
              {{ cotizacion.quote_number }} - {{ cotizacion.cliente?.name || 'Sin cliente' }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Nombre del evento</label>
          <input
            v-model="form.name"
            required
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          />
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Fecha</label>
          <input
            v-model="form.event_date"
            type="date"
            required
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          />
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Lugar</label>
          <input
            v-model="form.location"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          />
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Bartender</label>
          <input
            v-model="form.bartender_name"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          />
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Estado</label>
          <select
            v-model="form.status"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          >
            <option value="programado">Programado</option>
            <option value="confirmado">Confirmado</option>
            <option value="en_proceso">En proceso</option>
            <option value="completado">Completado</option>
            <option value="cancelado">Cancelado</option>
          </select>
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Notas</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          />
        </div>
      </div>

      <p v-if="error" class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">
        {{ error }}
      </p>

      <div class="mt-6 flex gap-3">
        <button
          type="submit"
          class="rounded-xl bg-purple-600 px-5 py-3 text-sm font-bold text-white hover:bg-purple-700"
          :disabled="saving"
        >
          {{ saving ? 'Guardando...' : isEditing ? 'Actualizar' : 'Crear' }}
        </button>

        <button
          v-if="isEditing"
          type="button"
          class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
          @click="resetForm"
        >
          Cancelar
        </button>
      </div>
    </form>

    <article class="rounded-2xl bg-white p-6 shadow-sm">
      <div class="mb-5 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-950">Eventos</h3>
        <span class="text-sm font-semibold text-slate-400">{{ eventos.length }} registros</span>
      </div>

      <div v-if="loading" class="py-10 text-center text-slate-500">Cargando eventos...</div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="border-b border-slate-100 text-slate-500">
              <th class="py-3 font-semibold">Evento</th>
              <th class="py-3 font-semibold">Cliente</th>
              <th class="py-3 font-semibold">Fecha</th>
              <th class="py-3 font-semibold">Estado</th>
              <th class="py-3 text-right font-semibold">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="evento in eventos" :key="evento.id" class="border-b border-slate-50">
              <td class="py-4">
                <p class="font-bold text-slate-900">{{ evento.name }}</p>
                <p class="text-xs text-slate-500">{{ evento.location || 'Sin lugar' }} · {{ evento.bartender_name || 'Sin bartender' }}</p>
              </td>
              <td class="py-4 text-slate-600">{{ evento.cliente?.name || 'Sin cliente' }}</td>
              <td class="py-4 text-slate-600">{{ evento.event_date }}</td>
              <td class="py-4">
                <span
                  class="rounded-full px-3 py-1 text-xs font-bold"
                  :class="statusClasses[evento.status] || 'bg-slate-100 text-slate-700'"
                >
                  {{ evento.status }}
                </span>
              </td>
              <td class="py-4 text-right">
                <button
                  class="mr-2 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                  @click="editEvento(evento)"
                >
                  Editar
                </button>
                <button
                  class="rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-600 hover:bg-red-100"
                  @click="deleteEvento(evento)"
                >
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>
  </section>
</template>
