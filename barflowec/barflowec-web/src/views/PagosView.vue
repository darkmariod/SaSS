<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/services/api'
import ProTable from '@/components/ProTable.vue'
import ToastNotification from '@/components/ToastNotification.vue'

const pagos = ref([])
const cotizaciones = ref([])
const eventos = ref([])
const loading = ref(true)
const saving = ref(false)
const editingId = ref(null)
const error = ref('')
const toast = ref({ message: '', type: 'success' })

const columns = [
  { key: 'cotizacion', label: 'Propuesta', class: 'hidden md:table-cell' },
  { key: 'amount', label: 'Monto', align: 'right' },
  { key: 'payment_method', label: 'Método' },
  { key: 'paid_at', label: 'Fecha' },
  { key: 'status', label: 'Estado' },
]

const form = reactive({
  cotizacion_id: '',
  evento_id: '',
  amount: 0,
  payment_method: 'efectivo',
  paid_at: '',
  reference: '',
  status: 'pendiente',
  notes: '',
})

const isEditing = computed(() => Boolean(editingId.value))

const statusClasses = {
  pendiente: 'bg-amber-50 text-amber-700',
  pagado: 'bg-emerald-50 text-emerald-700',
  anulado: 'bg-red-50 text-red-700',
}

const resetForm = () => {
  editingId.value = null
  form.cotizacion_id = ''
  form.evento_id = ''
  form.amount = 0
  form.payment_method = 'efectivo'
  form.paid_at = ''
  form.reference = ''
  form.status = 'pendiente'
  form.notes = ''
}

const fetchCotizaciones = async () => {
  const { data } = await api.get('/cotizaciones')
  cotizaciones.value = data.data || data
}

const fetchEventos = async () => {
  const { data } = await api.get('/eventos')
  eventos.value = data.data || data
}

const fetchPagos = async () => {
  const { data } = await api.get('/pagos')
  pagos.value = data.data || data
}

const loadData = async () => {
  loading.value = true
  await Promise.all([fetchCotizaciones(), fetchEventos(), fetchPagos()])
  loading.value = false
}

const submit = async () => {
  saving.value = true
  error.value = ''

  try {
    const payload = {
      ...form,
      cotizacion_id: form.cotizacion_id ? Number(form.cotizacion_id) : null,
      evento_id: form.evento_id ? Number(form.evento_id) : null,
      amount: Number(form.amount),
    }

    if (isEditing.value) {
      await api.put(`/pagos/${editingId.value}`, payload)
    } else {
      await api.post('/pagos', payload)
    }

    resetForm()
    await fetchPagos()
    toast.value = { message: 'Pago guardado correctamente.', type: 'success' }
  } catch (exception) {
    error.value = exception.response?.data?.message || 'No se pudo guardar el pago.'
  } finally {
    saving.value = false
  }
}

const editPago = (pago) => {
  editingId.value = pago.id
  form.cotizacion_id = pago.cotizacion_id || ''
  form.evento_id = pago.evento_id || ''
  form.amount = pago.amount || 0
  form.payment_method = pago.payment_method || 'efectivo'
  form.paid_at = pago.paid_at || ''
  form.reference = pago.reference || ''
  form.status = pago.status || 'pendiente'
  form.notes = pago.notes || ''
}

const deletePago = async (pago) => {
  if (!confirm(`Eliminar pago de $${Number(pago.amount || 0).toFixed(2)}?`)) return

  await api.delete(`/pagos/${pago.id}`)
  await fetchPagos()
}

const formatMoney = (value) => `$${Number(value || 0).toFixed(2)}`

onMounted(loadData)
</script>

<template>
  <section class="grid gap-6 xl:grid-cols-[430px_1fr]">
    <form class="rounded-2xl bg-white p-6 shadow-sm" @submit.prevent="submit">
      <div class="mb-5">
        <h3 class="text-lg font-bold text-slate-950">
          {{ isEditing ? 'Editar pago' : 'Nuevo pago' }}
        </h3>
        <p class="text-sm text-slate-500">Registra anticipos, cobros y saldos.</p>
      </div>

      <div class="space-y-4">
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
          <label class="text-sm font-semibold text-slate-700">Evento</label>
          <select
            v-model="form.evento_id"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          >
            <option value="">Sin evento</option>
            <option v-for="evento in eventos" :key="evento.id" :value="evento.id">
              {{ evento.name }} - {{ evento.event_date }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Monto</label>
          <input
            v-model="form.amount"
            type="number"
            min="0"
            step="0.01"
            required
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          />
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Método</label>
          <select
            v-model="form.payment_method"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          >
            <option value="efectivo">Efectivo</option>
            <option value="transferencia">Transferencia</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="otro">Otro</option>
          </select>
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Fecha de pago</label>
          <input
            v-model="form.paid_at"
            type="date"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          />
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Referencia</label>
          <input
            v-model="form.reference"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          />
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Estado</label>
          <select
            v-model="form.status"
            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
          >
            <option value="pendiente">Pendiente</option>
            <option value="pagado">Pagado</option>
            <option value="anulado">Anulado</option>
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
        <h3 class="text-lg font-bold text-slate-950">Pagos</h3>
        <span class="text-sm font-semibold text-slate-400">{{ pagos.length }} registros</span>
      </div>

      <ProTable
        :columns="columns"
        :rows="pagos"
        :loading="loading"
        empty-message="No hay pagos registrados."
      >
        <template #cell-cotizacion="{ row }">
          <p>{{ row.cotizacion?.quote_number || 'Sin cotización' }}</p>
          <p class="text-xs text-slate-500">{{ row.evento?.name || 'Sin evento' }}</p>
        </template>

        <template #cell-amount="{ row }">
          <p class="font-bold text-slate-900">{{ formatMoney(row.amount) }}</p>
          <p class="text-xs text-slate-500">{{ row.payment_method }} · {{ row.paid_at || 'sin fecha' }}</p>
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
          <button
            class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
            @click="editPago(row)"
          >
            Editar
          </button>
          <button
            class="rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-600 hover:bg-red-100"
            @click="deletePago(row)"
          >
            Eliminar
          </button>
        </template>
      </ProTable>
    </article>
  </section>
  <ToastNotification
    v-if="toast.message"
    :message="toast.message"
    :type="toast.type"
    @close="toast.message = ''"
  />
</template>
