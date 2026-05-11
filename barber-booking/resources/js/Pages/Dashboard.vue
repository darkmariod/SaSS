<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

const userRoles = computed(() => usePage().props.auth?.user?.roles ?? []);
const hasRole = (role) => userRoles.value.includes(role);
const isBarber = computed(() => hasRole('barber'));
const canAccessPanel = computed(() => hasRole('owner') || hasRole('admin') || hasRole('barber'));
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                BarberBooking EC
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Barbero: acceso rápido -->
                <div v-if="isBarber" class="mb-6">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                Bienvenido, {{ $page.props.auth.user.name }}
                            </h3>
                            <p class="text-gray-600 mb-4">
                                Desde aquí puedes ver y gestionar tus citas como barbero.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <Link
                                    :href="route('barber.appointments')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                                >
                                    Ver Mis Citas
                                </Link>
                                <Link
                                    v-if="canAccessPanel"
                                    :href="'/admin'"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition font-medium"
                                >
                                    Panel de Administración
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Owner/Admin: acceso rápido -->
                <div v-else-if="canAccessPanel">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                Bienvenido, {{ $page.props.auth.user.name }}
                            </h3>
                            <p class="text-gray-600 mb-4">
                                Accede al panel de administración para gestionar reservas, barberos, servicios y caja diaria.
                            </p>
                            <Link
                                :href="'/admin'"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition font-medium"
                            >
                                Ir al Panel Admin
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Otros roles -->
                <div v-else class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Bienvenido a BarberBooking EC.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
