<script setup>
defineProps({
    wizard: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <aside
        class="h-fit rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6 lg:sticky lg:top-8 lg:max-h-[calc(100vh-4rem)] lg:overflow-y-auto"
    >
        <h2 class="text-xl font-black tracking-tight sm:text-2xl">Resumen de la cita</h2>

        <div
            v-if="!wizard.bookableService.value"
            class="mt-6 rounded-xl border border-gray-200 p-6 text-lg text-gray-600"
        >
            Todavía no se han agregado servicios
        </div>

        <div
            v-else
            class="mt-6 overflow-hidden rounded-xl border border-gray-200"
        >
            <div class="flex items-center gap-4 border-b p-5">
                <div
                    v-if="wizard.selectedBarber.value"
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#eee4d5] font-black text-[#7c6b4f]"
                >
                    {{ wizard.selectedBarberName.value.substring(0, 2) }}
                </div>

                <div
                    v-else
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#eee4d5] font-black text-[#7c6b4f]"
                >
                    {{ wizard.bookableService.value.name.substring(0, 2) }}
                </div>

                <div>
                <p class="text-base font-black sm:text-lg">
                    {{ wizard.bookableService.value.name }}
                </p>

                <p class="text-sm text-gray-600 sm:text-base">
                    USD {{ Number(wizard.total.value).toFixed(2) }}
                    <span class="mx-1">·</span>
                    {{ wizard.totalDuration.value }} min
                </p>
                </div>
            </div>

            <div class="space-y-4 p-4 text-base sm:space-y-5 sm:p-5 sm:text-lg">
                <div>
                    <p class="font-black">
                        {{ wizard.bookableService.value.name }}
                    </p>

                <p class="text-gray-600">
                    {{ wizard.selectedBarberName.value }}
                </p>
                </div>

                <div v-if="wizard.selectedAddon.value">
                    <p class="font-black">Extra</p>

                <p class="text-gray-600">
                    {{ wizard.selectedAddon.value.name }}
                </p>
                </div>

                <div v-if="wizard.selectedDate.value">
                    <p class="font-black">Fecha</p>

                <p class="text-gray-600">
                    {{ wizard.formatDate(wizard.selectedDate.value) }}
                </p>
                </div>

                <div v-if="wizard.selectedSlot.value">
                    <p class="font-black">Hora</p>

                <p class="text-gray-600">
                    {{
                        wizard.formatTime(
                            wizard.selectedSlot.value.start_time,
                        )
                    }}
                    -
                    {{
                        wizard.formatTime(
                            wizard.selectedSlot.value.end_time,
                        )
                    }}
                </p>
                </div>

                <div class="border-t pt-5">
                    <div class="flex justify-between text-sm sm:text-base">
                        <span>Subtotal</span>
                        <span
                            >USD
                            {{ Number(wizard.total.value).toFixed(2) }}</span>
                        >
                    </div>

                    <div class="mt-2 flex justify-between text-sm sm:mt-3 sm:text-base">
                        <span>Impuestos</span>
                        <span>USD 0.00</span>
                    </div>

                    <div class="mt-2 flex justify-between font-black text-sm sm:mt-3 sm:text-base">
                        <span>Total</span>
                        <span
                            >USD
                            {{ Number(wizard.total.value).toFixed(2) }}</span>
                        >
                    </div>

                    <div class="mt-6 border-t pt-5">
                        <div class="flex justify-between font-black">
                            <span>A pagar hoy</span>
                            <span>USD {{
                                wizard.payToday.value
                                    ? Number(wizard.total.value).toFixed(2)
                                    : "0.00"
                            }}</span>
                        </div>

                        <div class="mt-2 flex justify-between text-gray-600">
                            <span>A pagar en la cita</span>
                            <span>USD {{
                                wizard.payAtAppointment.value
                                    ? Number(wizard.total.value).toFixed(2)
                                    : "0.00"
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button
            v-if="wizard.step.value === 'services'"
            type="button"
            class="mt-4 w-full rounded-xl px-6 py-4 text-base font-black transition-all duration-200 ease-in-out sm:text-lg focus:outline-none focus:ring-2 focus:ring-[#d8c59f] focus:ring-offset-2"
            :class="
                wizard.canContinueFromServices.value
                    ? 'bg-[#d8c59f] text-gray-950 hover:bg-[#cdb88c] hover:shadow-lg active:scale-[0.98] cursor-pointer'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
            "
            :disabled="!wizard.canContinueFromServices.value"
            @click="wizard.goToOptions"
        >
            Siguiente
        </button>

        <button
            v-if="wizard.step.value === 'options'"
            type="button"
            class="mt-4 w-full rounded-xl px-6 py-4 text-base font-black transition-all duration-200 ease-in-out sm:text-lg focus:outline-none focus:ring-2 focus:ring-[#d8c59f] focus:ring-offset-2"
            :class="
                wizard.canContinueFromOptions.value
                    ? 'bg-[#d8c59f] text-gray-950 hover:bg-[#cdb88c] hover:shadow-lg active:scale-[0.98] cursor-pointer'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
            "
            :disabled="!wizard.canContinueFromOptions.value"
            @click="wizard.goToStaff"
        >
            Agregar
        </button>

        <button
            v-if="wizard.step.value === 'staff'"
            type="button"
            class="mt-4 w-full rounded-xl px-6 py-4 text-base font-black transition-all duration-200 ease-in-out sm:text-lg focus:outline-none focus:ring-2 focus:ring-[#d8c59f] focus:ring-offset-2"
            :class="
                wizard.canContinueFromStaff.value
                    ? 'bg-[#d8c59f] text-gray-950 hover:bg-[#cdb88c] hover:shadow-lg active:scale-[0.98] cursor-pointer'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
            "
            :disabled="!wizard.canContinueFromStaff.value"
            @click="wizard.goToSchedule"
        >
            Agregar
        </button>

        <button
            v-if="wizard.step.value === 'schedule'"
            type="button"
            class="mt-4 w-full rounded-xl px-6 py-4 text-base font-black transition-all duration-200 ease-in-out sm:text-lg focus:outline-none focus:ring-2 focus:ring-[#d8c59f] focus:ring-offset-2"
            :class="
                wizard.canContinueFromSchedule.value
                    ? 'bg-[#d8c59f] text-gray-950 hover:bg-[#cdb88c] hover:shadow-lg active:scale-[0.98] cursor-pointer'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
            "
            :disabled="!wizard.canContinueFromSchedule.value"
            @click="wizard.goToContact"
        >
            Siguiente
        </button>

        <button
            v-if="wizard.step.value === 'contact'"
            type="button"
            class="mt-4 w-full rounded-xl px-6 py-4 text-base font-black transition-all duration-200 ease-in-out sm:text-lg focus:outline-none focus:ring-2 focus:ring-[#d8c59f] focus:ring-offset-2"
            :class="
                wizard.canCreateReservation.value
                    ? 'bg-[#d8c59f] text-gray-950 hover:bg-[#cdb88c] hover:shadow-lg active:scale-[0.98] cursor-pointer'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
            "
            :disabled="
                !wizard.canCreateReservation.value ||
                wizard.creatingReservation.value
            "
            @click="wizard.createReservation"
        >
            {{
                wizard.creatingReservation.value
                    ? "Reservando..."
                    : "Reservar cita"
            }}
        </button>
    </aside>
</template>
