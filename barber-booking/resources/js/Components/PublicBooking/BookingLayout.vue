<script setup>
defineProps({
    shop: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="min-h-screen bg-white text-gray-950">
        <header class="border-b border-gray-200 bg-white shadow-sm">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-6 sm:flex-row sm:items-center sm:justify-between lg:px-8"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.22em] text-gray-600"
                    >
                        Reserva online
                    </p>

                    <h1
                        class="mt-2 text-2xl font-black tracking-tight sm:text-3xl"
                    >
                        {{ shop.name }}
                    </h1>

                    <p class="mt-2 text-sm text-gray-600 sm:text-base">
                        {{ shop.city || "Ciudad no registrada" }}
                        <span class="mx-1">·</span>
                        {{ shop.address || "Dirección no registrada" }}
                    </p>
                </div>

                <div class="text-sm text-gray-600 sm:text-right">
                    <p class="font-semibold text-gray-700">
                        {{ shop.phone || "Teléfono no registrado" }}
                    </p>

                    <p class="mt-1 max-w-md">
                        {{
                            shop.description ||
                            "Agenda tu cita de forma rápida y sencilla."
                        }}
                    </p>
                </div>
            </div>
        </header>

        <main
            class="mx-auto grid max-w-7xl gap-6 overflow-x-hidden px-4 py-6 sm:px-5 sm:py-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:gap-8 lg:px-8 lg:py-12"
        >
            <slot />
        </main>
    </div>
</template>
