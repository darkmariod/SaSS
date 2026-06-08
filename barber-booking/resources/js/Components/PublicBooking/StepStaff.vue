<script setup>
defineProps({
    wizard: {
        type: Object,
        required: true,
    },
    barbers: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div>
        <button
            type="button"
            class="mb-8 inline-flex items-center gap-2 text-lg font-black text-gray-600 hover:text-gray-950"
            @click="wizard.step.value = 'services'"
        >
            ← Servicio
        </button>

        <h2 class="text-4xl font-black tracking-tight">Personal</h2>

        <p
            v-if="!wizard.canContinueFromStaff.value"
            class="mt-4 text-xl font-black text-red-600"
        >
            ⚠ Selecciona al personal.
        </p>

        <div class="mt-8 space-y-5">
                 <button
                     type="button"
                     class="flex min-h-[88px] w-full items-center justify-between rounded-2xl p-5 transition hover:bg-gray-50"
                     :class="
                    wizard.staffMode.value === 'any'
                        ? 'bg-[#f5f0e8]'
                        : 'bg-white'
                "
                @click="wizard.selectAnyStaff"
            >
                <div class="flex items-center gap-6">
                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-[#eee4d5] text-3xl font-black text-[#7c6b4f]"
                    >
                        👥
                    </div>

                    <div>
                        <p class="text-2xl font-black">
                            Cualquier miembro del personal
                        </p>

                        <p class="mt-1 text-gray-600">
                            El sistema asignará el primer barbero disponible.
                        </p>
                    </div>
                </div>

                <div
                    class="h-11 w-11 shrink-0 rounded-full border-4 transition"
                    :class="
                        wizard.staffMode.value === 'any'
                            ? 'border-gray-950 bg-gray-950'
                            : 'border-gray-300'
                    "
                />
            </button>

                 <button
                     v-for="barber in barbers"
                     :key="barber.id"
                     type="button"
                     class="flex min-h-[88px] w-full items-center justify-between rounded-2xl p-5 transition hover:bg-gray-50"
                     :class="
                    wizard.selectedBarber.value?.id === barber.id
                        ? 'bg-[#f5f0e8]'
                        : 'bg-white'
                "
                @click="wizard.selectBarber(barber)"
            >
                <div class="flex items-center gap-6">
                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-[#eee4d5] text-2xl font-black text-[#7c6b4f]"
                    >
                        {{ barber.display_name.substring(0, 2) }}
                    </div>

                    <div>
                        <p class="text-2xl font-black">
                            {{ barber.display_name }}
                        </p>

                        <p class="mt-1 text-gray-600">
                            {{ barber.bio || "Barbero profesional" }}
                        </p>
                    </div>
                </div>

                <div
                     class="h-11 w-11 shrink-0 rounded-full border-4 transition sm:h-12 sm:w-12"
                     :class="
                         wizard.selectedBarber.value?.id === barber.id
                             ? 'border-gray-950 bg-gray-950'
                             : 'border-gray-300'
                     "
                 />
            </button>
        </div>
    </div>
</template>
