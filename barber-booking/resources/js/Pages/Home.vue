<script setup>
import { Head } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

<template>
    <Head title="BarberBooking EC - Inicio" />

    <GuestLayout>
        <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 py-6 px-4 sm:py-12">
            <!-- Logo -->
            <div class="mb-8 w-full max-w-xs sm:max-w-md">
                <ApplicationLogo class="w-full h-auto" />
            </div>

            <!-- Título -->
            <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center sm:text-3xl">
                BarberBooking EC
            </h1>
            <p class="text-gray-600 mb-10 text-center max-w-md">
                Sistema de reservas para barberías. Agenda tu cita o gestiona tu negocio.
            </p>

            <!-- Botones (Stack en móvil, fila en desktop) -->
            <div class="w-full max-w-md flex flex-col gap-4 sm:flex-row sm:justify-center">
                <!-- Botón: Ir a Reservas -->
                <a
                    href="/barberia/barberia-demo"
                    class="inline-flex items-center justify-center px-6 py-4 bg-gray-800 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 text-lg w-full sm:w-auto"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3-3a6 6 0 01-7.192 7.192l-4.08 4.08a4.5 4.5 0 01-6.364-6.364l1.966-1.966a4.5 4.5 0 015.656 5.656l-1.06 1.06a3 3 0 104.243 4.243l3.314-3.313a4.5 4.5 0 00-6.586-6.586l-1.06 1.06a3 3 0 11-4.243-4.243l4.08-4.08a6 6 0 017.192 7.192l-4.08 4.08" />
                    </svg>
                    Ir a Reservas
                </a>

                <!-- Botón: Soy Administrador -->
                <a
                    href="/admin/login"
                    class="inline-flex items-center justify-center px-6 py-4 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:bg-gray-50 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 text-lg w-full sm:w-auto"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-7.5-6.75a4.5 4.5 0 10-4.5 4.5c0 1.34.328 2.605.94 3.598m-7.5 6.75a4.5 4.5 0 10-4.5 4.5c0 1.34.328 2.605.94 3.598m7.5-6.75a4.5 4.5 0 017.5 0m-7.5 0a4.5 4.5 0 017.5 0m-7.5 0a4.5 4.5 0 017.5 0" />
                    </svg>
                    Soy Administrador
                </a>
            </div>

            <!-- Texto adicional para móviles -->
            <p class="mt-8 text-sm text-gray-500 text-center max-w-md">
                ¿Eres cliente? Toca "Ir a Reservas" para agendar tu cita.<br>
                ¿Eres dueño o barbero? Usa "Soy Administrador".
            </p>
        </div>
    </GuestLayout>
</template>
