<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/services/api'
import ProTable from '@/components/ProTable.vue'
import ToastNotification from '@/components/ToastNotification.vue'

const eventos = ref([])
const clientes = ref([])
const cotizaciones = ref([])
const loading = ref(true)
const saving = ref(false)
const editingId = ref(null)
const error = ref('')
const toast = ref({ message: '', type: 'success' })

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
    toast.value = { message: 'Evento guardado correctamente.', type: 'success' }
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

            <ProTable
                :columns="[
                    { key: 'name', label: 'Evento' },
                    { key: 'cliente', label: 'Cliente', class: 'hidden md:table-cell' },
                    { key: 'event_date', label: 'Fecha' },
                    { key: 'location', label: 'Ubicación', class: 'hidden lg:table-cell' },
                    { key: 'status', label: 'Estado' },
                ]"
                :rows="eventos"
                :loading="loading"
                empty-message="No hay eventos registrados."
                empty-icon="📅"
            >
                <template #cell-name="{ row }">
                    <p class="font-bold text-slate-900">{{ row.name }}</p>
                    <p class="text-xs text-slate-400">{{ row.bartender_name || 'Sin bartender' }}</p>
                </template>
                <template #cell-cliente="{ row }">
                    <span class="text-slate-600">{{ row.cliente?.name || 'Sin cliente' }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        class="rounded-full px-3 py-1 text-xs font-bold"
                        :class="statusClasses[row.status] || 'bg-slate-100 text-slate-700'"
                    >
                        {{ row.status }}
                    </span>
                </template>
                <template #actions="{ row }">
                    <button class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="editEvento(row)">Editar</button>
                    <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100" @click="deleteEvento(row)">Eliminar</button>
                </template>
            </ProTable>
    </article>
  </section>

  <ToastNotification :message="toast.message" :type="toast.type" @close="toast.message = ''" />
</template>
