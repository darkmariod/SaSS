<script setup>
defineProps({
    wizard: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div>
        <button
            type="button"
            class="mb-8 inline-flex items-center gap-2 text-lg font-black text-gray-600 hover:text-gray-950"
            @click="wizard.goToServices"
        >
            ← Todos los servicios
        </button>

        <h2 class="text-4xl font-black tracking-tight sm:text-5xl">
            {{ wizard.selectedMainService.value?.name }}
        </h2>

        <p class="mt-4 text-xl text-gray-700">
            El precio varía
            <span class="mx-2">·</span>
            {{ wizard.selectedMainService.value?.duration_minutes }} min+
        </p>

        <h3 class="mt-12 text-3xl font-black">Opciones</h3>

        <div class="mt-5 divide-y divide-gray-200">
            <button
                v-for="option in wizard.serviceOptions.value"
                :key="option.id"
                type="button"
                class="group flex w-full items-center justify-between gap-6 py-7 text-left transition hover:bg-gray-50"
                @click="wizard.selectServiceOption(option)"
            >
                <div>
                    <h4
                        class="text-2xl font-black tracking-tight group-hover:underline"
                    >
                        {{ option.name }}
                    </h4>

                        <p class="mt-2 text-xl text-gray-700">
                            USD {{ Number(option.price).toFixed(2) }}
                            <span class="mx-2">·</span>
                            {{ option.duration_minutes }} min
                        </p>

                        <p
                            v-if="option.description"
                            class="mt-2 max-w-xl text-gray-600"
                        >
                        {{ option.description }}
                    </p>
                </div>

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-4 transition"
                    :class="
                        wizard.selectedServiceOption.value?.id === option.id
                            ? 'border-gray-950 bg-gray-950'
                            : 'border-gray-300 group-hover:border-gray-950'
                    "
                >
                    <div
                        v-if="
                            wizard.selectedServiceOption.value?.id === option.id
                        "
                        class="h-2.5 w-2.5 rounded-full bg-white"
                    />
                </div>
            </button>
        </div>

        <div v-if="wizard.addons.value.length" class="mt-12">
            <h3 class="text-3xl font-black">¿Agregar más a tu cita?</h3>

            <div class="mt-5 divide-y divide-gray-200">
                <button
                    v-for="addon in wizard.addons.value"
                    :key="addon.id"
                    type="button"
                    class="group flex w-full items-center justify-between gap-6 rounded-2xl px-4 py-6 text-left transition hover:bg-[#f7f2ea]"
                    :class="
                        wizard.selectedAddon.value?.id === addon.id
                            ? 'bg-[#f5f0e8]'
                            : ''
                    "
                    @click="wizard.toggleAddon(addon)"
                >
                    <div>
                        <h4 class="text-xl font-black group-hover:underline">
                            {{ addon.name }}
                        </h4>

                        <p class="mt-2 max-w-xl text-gray-600">
                            {{ addon.description }}
                        </p>

                        <p class="mt-3 text-gray-700">
                            USD {{ Number(addon.price).toFixed(2) }}
                            <span class="mx-2">·</span>
                            {{ addon.duration_minutes }} min
                        </p>

                        <p
                            v-if="wizard.selectedAddon.value?.id === addon.id"
                            class="mt-2 text-sm font-black text-green-700"
                        >
                            ✓ Añadido
                        </p>
                    </div>

                    <div
                        class="h-11 w-11 shrink-0 rounded-full border-4 transition"
                        :class="
                            wizard.selectedAddon.value?.id === addon.id
                                ? 'border-gray-950 bg-gray-950'
                                : 'border-gray-300 group-hover:border-gray-950'
                        "
                    />
                </button>
            </div>
        </div>
    </div>
</template>
