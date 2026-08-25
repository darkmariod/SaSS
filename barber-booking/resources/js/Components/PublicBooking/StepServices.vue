<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    wizard: {
        type: Object,
        required: true,
    },
});

/**
 * Lista plana, como la de cualquier página de reservas: el cliente ve los
 * cortes con su precio y elige uno. La jerarquía interna (servicio principal
 * que agrupa opciones) se resuelve por detrás.
 */
const servicios = computed(() => props.wizard.bookableServices.value);

const categorias = computed(() => [
    ...new Set(servicios.value.map((s) => s.category || "General")),
]);

const categoriaActiva = ref(categorias.value[0] || "General");

const visibles = computed(() =>
    categorias.value.length > 1
        ? servicios.value.filter((s) => (s.category || "General") === categoriaActiva.value)
        : servicios.value,
);

const elegido = (servicio) =>
    Number(props.wizard.bookableService.value?.id) === Number(servicio.id);

const precio = (v) => `$${Number(v).toFixed(2)}`;
</script>

<template>
    <div>
        <h2 class="text-2xl font-black tracking-tight sm:text-4xl">Elegí tu servicio</h2>

        <!-- Las categorías solo aparecen si de verdad hay más de una: una sola
             pestaña es ruido que no ayuda a decidir. -->
        <div
            v-if="categorias.length > 1"
            class="mt-5 flex gap-5 overflow-x-auto border-b border-gray-200 text-sm font-black text-gray-600 sm:mt-7 sm:gap-7 sm:text-base"
        >
            <button
                v-for="categoria in categorias"
                :key="categoria"
                type="button"
                class="whitespace-nowrap border-b-2 pb-3 transition"
                :class="categoriaActiva === categoria
                    ? 'border-gray-950 text-gray-950'
                    : 'border-transparent hover:text-gray-950'"
                @click="categoriaActiva = categoria"
            >
                {{ categoria }}
            </button>
        </div>

        <div class="mt-4 divide-y divide-gray-200 sm:mt-6">
            <button
                v-for="servicio in visibles"
                :key="servicio.id"
                type="button"
                class="group flex w-full items-center justify-between gap-4 py-5 text-left transition hover:bg-gray-50 sm:gap-6 sm:py-6"
                @click="wizard.selectBookable(servicio)"
            >
                <div class="min-w-0">
                    <h3 class="text-lg font-bold tracking-tight sm:text-xl">
                        {{ servicio.name }}
                    </h3>

                    <p
                        v-if="servicio.description"
                        class="mt-1 max-w-xl text-sm leading-relaxed text-gray-600"
                    >
                        {{ servicio.description }}
                    </p>

                    <p class="mt-2 text-sm text-gray-800 sm:text-base">
                        <span class="font-bold">{{ precio(servicio.price) }}</span>
                        <span class="mx-2 text-gray-400">·</span>
                        <span class="text-gray-600">{{ servicio.duration_minutes }} min</span>
                    </p>
                </div>

                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 transition sm:h-11 sm:w-11"
                    :class="elegido(servicio)
                        ? 'border-gray-950 bg-gray-950'
                        : 'border-gray-300 group-hover:border-gray-950'"
                >
                    <svg
                        v-if="elegido(servicio)"
                        class="h-5 w-5 text-white"
                        viewBox="0 0 20 20"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="3"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5l4 4 8-9" />
                    </svg>
                </div>
            </button>
        </div>

        <p v-if="! visibles.length" class="mt-8 text-gray-500">
            Esta barbería todavía no cargó sus servicios.
        </p>
    </div>
</template>
