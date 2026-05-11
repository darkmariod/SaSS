<script setup>
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed } from "vue";

const props = defineProps({
  reservations: {
    type: Array,
    required: true,
  },
});

const TODAY = new Date().toISOString().split('T')[0];

const filterStatus = ref('all');
const filterDate = ref('today');
const loading = ref(null);
const showReceipt = ref(null);

const filteredReservations = computed(() => {
  let result = props.reservations;

  if (filterDate.value === 'today') {
    result = result.filter((r) => r.reservation_date === TODAY);
  }

  if (filterStatus.value !== 'all') {
    result = result.filter((r) => r.reservation_status === filterStatus.value);
  }

  return result;
});

const todayCount = computed(() => {
  return props.reservations.filter((r) => r.reservation_date === TODAY).length;
});

const paymentLabel = (status) => {
  const labels = {
    pendiente: 'Pendiente',
    comprobante_subido: 'Comprobante subido',
    pagado: 'Pagado',
    rechazado: 'Rechazado',
    fallido: 'Fallido',
  };
  return labels[status] || status;
};

const receiptUrl = (reservation) => {
  const path = reservation.detail?.receipt_image || reservation.transfer?.receipt_image_path;
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return '/storage/' + path;
};

async function confirmReservation(id) {
  if (!confirm('¿Confirmar esta reserva?')) return;
  loading.value = id;
  try {
    await router.patch(route('barber.reservations.update-status', { reservation: id }), {
      reservation_status: 'confirmada',
    });
  } catch (e) {
    alert('Error al confirmar la reserva');
  } finally {
    loading.value = null;
  }
}

async function cancelReservation(id) {
  const reason = prompt('Motivo de cancelación (opcional):');
  loading.value = id;
  try {
    await router.patch(route('barber.reservations.update-status', { reservation: id }), {
      reservation_status: 'cancelada',
    });
  } catch (e) {
    alert('Error al cancelar la reserva');
  } finally {
    loading.value = null;
  }
}

async function confirmPayment(id) {
  if (!confirm('¿Confirmar el pago de esta reserva?')) return;
  loading.value = 'pay-' + id;
  try {
    await router.post(route('barber.reservations.confirm-payment', { reservation: id }));
  } catch (e) {
    alert('Error al confirmar el pago');
  } finally {
    loading.value = null;
  }
}

async function rejectPayment(id) {
  if (!confirm('¿Rechazar el pago de esta reserva? Se cancelará la cita.')) return;
  loading.value = 'pay-' + id;
  try {
    await router.post(route('barber.reservations.reject-payment', { reservation: id }));
  } catch (e) {
    alert('Error al rechazar el pago');
  } finally {
    loading.value = null;
  }
}
</script>

<template>
  <Head title="Mis Citas" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Mis Citas
      </h2>
    </template>

    <div class="mb-4 text-sm text-gray-600">
      Tienes <strong>{{ todayCount }}</strong> cita{{ todayCount !== 1 ? 's' : '' }} para hoy
    </div>

    <div class="mb-6 flex flex-wrap gap-2 sm:gap-4">
      <button
        @click="filterDate = 'today'"
        :class="filterDate === 'today' ? 'bg-blue-600 text-white' : 'bg-white text-gray-800 border border-gray-300'"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition"
      >
        Hoy
      </button>
      <button
        @click="filterDate = 'all'"
        :class="filterDate === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-800 border border-gray-300'"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition"
      >
        Todas las fechas
      </button>

      <span class="w-px bg-gray-300 mx-1"></span>

      <button
        @click="filterStatus = 'all'"
        :class="filterStatus === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-800 border border-gray-300'"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition"
      >
        Todos
      </button>
      <button
        @click="filterStatus = 'pendiente'"
        :class="filterStatus === 'pendiente' ? 'bg-yellow-500 text-white' : 'bg-white text-gray-800 border border-gray-300'"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition"
      >
        Pendientes
      </button>
      <button
        @click="filterStatus = 'confirmada'"
        :class="filterStatus === 'confirmada' ? 'bg-green-500 text-white' : 'bg-white text-gray-800 border border-gray-300'"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition"
      >
        Confirmadas
      </button>
      <button
        @click="filterStatus = 'cancelada'"
        :class="filterStatus === 'cancelada' ? 'bg-red-500 text-white' : 'bg-white text-gray-800 border border-gray-300'"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition"
      >
        Canceladas
      </button>
    </div>

    <div class="space-y-4">
      <div
        v-for="reservation in filteredReservations()"
        :key="reservation.id"
        class="bg-white rounded-xl shadow p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-4"
      >
        <div class="flex-shrink-0 text-center">
          <div class="text-2xl font-bold text-gray-800">
            {{ new Date(reservation.reservation_date).toLocaleDateString('es-EC', { day: 'numeric' }) }}
          </div>
          <div class="text-sm text-gray-500">
            {{ new Date(reservation.reservation_date).toLocaleDateString('es-EC', { month: 'short' }) }}
          </div>
        </div>

        <div class="flex-1 min-w-0">
          <div class="font-semibold text-gray-800 text-lg">
            {{ reservation.customer_name }}
          </div>
          <div class="text-gray-600 text-sm">
            {{ reservation.service.name }} — {{ reservation.start_time.slice(0, 5) }} - {{ reservation.end_time.slice(0, 5) }}
          </div>
          <div class="text-gray-500 text-sm">
            {{ reservation.customer_phone }}
          </div>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span
              :class="{
                'px-2 py-0.5 rounded-full text-xs font-semibold': true,
                'bg-yellow-100 text-yellow-800': reservation.reservation_status === 'pendiente',
                'bg-green-100 text-green-800': reservation.reservation_status === 'confirmada',
                'bg-red-100 text-red-800': reservation.reservation_status === 'cancelada',
                'bg-blue-100 text-blue-800': reservation.reservation_status === 'completada',
              }"
            >
              {{ reservation.reservation_status === 'pendiente' ? 'Pendiente' : reservation.reservation_status === 'confirmada' ? 'Confirmada' : reservation.reservation_status === 'cancelada' ? 'Cancelada' : 'Completada' }}
            </span>
            <span
              :class="{
                'px-2 py-0.5 rounded-full text-xs font-semibold': true,
                'bg-gray-100 text-gray-600': reservation.payment_status === 'pendiente',
                'bg-blue-100 text-blue-800': reservation.payment_status === 'comprobante_subido',
                'bg-green-100 text-green-800': reservation.payment_status === 'pagado',
                'bg-red-100 text-red-800': reservation.payment_status === 'rechazado' || reservation.payment_status === 'fallido',
              }"
            >
              {{ paymentLabel(reservation.payment_status) }}
            </span>
          </div>
        </div>

        <div class="flex flex-wrap gap-2 flex-shrink-0">
          <button
            v-if="reservation.payment_status === 'comprobante_subido' && receiptUrl(reservation)"
            @click="showReceipt = reservation.id"
            class="px-3 py-1 bg-blue-500 text-white rounded-lg text-sm font-semibold hover:bg-blue-600 transition"
          >
            Ver comprobante
          </button>
          <button
            v-if="reservation.payment_status === 'comprobante_subido'"
            @click="confirmPayment(reservation.id)"
            :disabled="loading === 'pay-' + reservation.id"
            class="px-3 py-1 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 disabled:opacity-50 transition"
          >
            {{ loading === 'pay-' + reservation.id ? '...' : 'Aprobar pago' }}
          </button>
          <button
            v-if="reservation.payment_status === 'comprobante_subido'"
            @click="rejectPayment(reservation.id)"
            :disabled="loading === 'pay-' + reservation.id"
            class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 disabled:opacity-50 transition"
          >
            {{ loading === 'pay-' + reservation.id ? '...' : 'Rechazar' }}
          </button>
          <button
            v-if="reservation.reservation_status === 'pendiente'"
            @click="confirmReservation(reservation.id)"
            :disabled="loading === reservation.id"
            class="px-3 py-1 bg-green-500 text-white rounded-lg text-sm font-semibold hover:bg-green-600 disabled:opacity-50 transition"
          >
            {{ loading === reservation.id ? '...' : 'Confirmar' }}
          </button>
          <button
            v-if="reservation.reservation_status !== 'cancelada' && reservation.reservation_status !== 'completada'"
            @click="cancelReservation(reservation.id)"
            :disabled="loading === reservation.id"
            class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 disabled:opacity-50 transition"
          >
            {{ loading === reservation.id ? '...' : 'Cancelar' }}
          </button>
        </div>
      </div>

      <div
        v-if="filteredReservations().length === 0"
        class="text-center py-12 text-gray-500"
      >
        No tienes citas {{ filterStatus === 'all' ? '' : 'con este estado' }}.
      </div>
    </div>

    <!-- Modal para ver comprobante -->
    <Teleport to="body">
      <div
        v-if="showReceipt"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        @click.self="showReceipt = null"
      >
        <div class="relative max-w-2xl w-full bg-white rounded-2xl overflow-hidden shadow-2xl">
          <button
            @click="showReceipt = null"
            class="absolute top-3 right-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 transition"
          >
            ✕
          </button>
          <div v-if="showReceipt" class="flex items-center justify-center p-4">
            <img
              :src="receiptUrl(filteredReservations().find(r => r.id === showReceipt))"
              alt="Comprobante de pago"
              class="max-h-[80vh] w-auto rounded-xl object-contain"
            />
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>
