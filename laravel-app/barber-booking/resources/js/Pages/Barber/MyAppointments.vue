<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref } from "vue";

const props = defineProps({
  reservations: {
    type: Array,
    required: true,
  },
});

const filterStatus = ref('all');

const filteredReservations = () => {
  if (filterStatus.value === 'all') return props.reservations;
  return props.reservations.filter((r) => r.reservation_status === filterStatus.value);
};
</script>

<template>
  <Head title="Mis Citas" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Mis Citas
      </h2>
    </template>

    <!-- Filtros móviles -->
    <div class="mb-6 flex flex-wrap gap-2 sm:gap-4">
      <button
        @click="filterStatus = 'all'"
        :class="filterStatus === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-800 border border-gray-300'"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition"
      >
        Todas
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

    <!-- Lista de citas (responsive) -->
    <div class="space-y-4">
      <div
        v-for="reservation in filteredReservations()"
        :key="reservation.id"
        class="bg-white rounded-xl shadow p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-4"
      >
        <!-- Fecha -->
        <div class="flex-shrink-0 text-center">
          <div class="text-2xl font-bold text-gray-800">
            {{ new Date(reservation.reservation_date).toLocaleDateString('es-EC', { day: 'numeric' }) }}
          </div>
          <div class="text-sm text-gray-500">
            {{ new Date(reservation.reservation_date).toLocaleDateString('es-EC', { month: 'short' }) }}
          </div>
        </div>

        <!-- Info -->
        <div class="flex-1">
          <div class="font-semibold text-gray-800 text-lg">
            {{ reservation.customer_name }}
          </div>
          <div class="text-gray-600 text-sm">
            {{ reservation.service.name }} — {{ reservation.start_time.slice(0, 5) }} - {{ reservation.end_time.slice(0, 5) }}
          </div>
          <div class="text-gray-500 text-sm">
            {{ reservation.customer_phone }}
          </div>
        </div>

        <!-- Estado -->
        <div class="flex-shrink-0">
          <span
            :class="{
              'px-3 py-1 rounded-full text-xs font-semibold': true,
              'bg-yellow-100 text-yellow-800': reservation.reservation_status === 'pendiente',
              'bg-green-100 text-green-800': reservation.reservation_status === 'confirmada',
              'bg-red-100 text-red-800': reservation.reservation_status === 'cancelada',
            }"
          >
            {{ reservation.reservation_status === 'pendiente' ? 'Pendiente' : reservation.reservation_status === 'confirmada' ? 'Confirmada' : 'Cancelada' }}
          </span>
        </div>

        <!-- Acciones (solo si está pendiente) -->
        <div class="flex gap-2 flex-shrink-0">
          <button
            v-if="reservation.reservation_status === 'pendiente'"
            @click="confirmReservation(reservation.id)"
            class="px-3 py-1 bg-green-500 text-white rounded-lg text-sm font-semibold hover:bg-green-600 transition"
          >
            Confirmar
          </button>
          <button
            v-if="reservation.reservation_status !== 'cancelada'"
            @click="cancelReservation(reservation.id)"
            class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 transition"
          >
            Cancelar
          </button>
        </div>
      </div>

      <!-- Sin citas -->
      <div
        v-if="filteredReservations().length === 0"
        class="text-center py-12 text-gray-500"
      >
        No tienes citas {{ filterStatus === 'all' ? '' : 'con este estado' }}.
      </div>
    </div>
  </AuthenticatedLayout>
</template>
