<script setup>
import { computed } from "vue";
import { Head } from "@inertiajs/vue3";
import { useBookingWizard } from "@/Composables/useBookingWizard";

import BookingLayout from "@/Components/PublicBooking/BookingLayout.vue";
import BookingSummary from "@/Components/PublicBooking/BookingSummary.vue";

import StepServices from "@/Components/PublicBooking/StepServices.vue";
import StepStaff from "@/Components/PublicBooking/StepStaff.vue";
import StepSchedule from "@/Components/PublicBooking/StepSchedule.vue";
import StepCheckout from "@/Components/PublicBooking/StepCheckout.vue";

const props = defineProps({
    shop: {
        type: Object,
        required: true,
    },
    services: {
        type: Array,
        required: true,
    },
    barbers: {
        type: Array,
        required: true,
    },
    preselectedBarber: {
        type: Object,
        default: null,
    },
});

const wizard = useBookingWizard(props, props.preselectedBarber);

const steps = [
    { key: "services", label: "Servicio", icon: "1" },
    { key: "staff", label: "Barbero", icon: "2" },
    { key: "schedule", label: "Horario", icon: "3" },
    { key: "checkout", label: "Confirmar", icon: "4" },
];

const currentStepIndex = computed(() => {
    return steps.findIndex((s) => s.key === wizard.step.value);
});
</script>

<template>
    <Head :title="shop.name" />

    <BookingLayout :shop="shop">
        <section>
            <!-- Step Progress Indicator (hidden on confirmation) -->
            <div
                v-if="wizard.step.value !== 'checkout' || !wizard.reservationCreated.value"
                class="mb-8"
            >
                <div class="flex items-center justify-center gap-0 sm:gap-2">
                    <template
                        v-for="(step, index) in steps.slice(0, -1)"
                        :key="step.key"
                    >
                        <button
                            type="button"
                            :class="[
                                'flex h-8 w-8 items-center justify-center rounded-full text-xs font-black transition-all duration-300 sm:h-10 sm:w-10 sm:text-sm',
                                index < currentStepIndex
                                    ? 'bg-[#d8c59f] text-gray-950'
                                    : index === currentStepIndex
                                      ? 'bg-gray-950 text-white ring-2 ring-[#d8c59f] ring-offset-2'
                                      : 'bg-gray-100 text-gray-400',
                            ]"
                            :disabled="index > currentStepIndex"
                            @click="
                                index < currentStepIndex &&
                                    (wizard.step.value = step.key)
                            "
                        >
                            <svg
                                v-if="index < currentStepIndex"
                                class="h-4 w-4 sm:h-5 sm:w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="3"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 12.75l6 6 9-13.5"
                                />
                            </svg>
                            <span v-else>{{ step.icon }}</span>
                        </button>

                        <div
                            :class="[
                                'h-0.5 flex-1 transition-all duration-500 sm:mx-1',
                                index < currentStepIndex
                                    ? 'bg-[#d8c59f]'
                                    : 'bg-gray-200',
                            ]"
                        />
                    </template>

                    <!-- Last step -->
                    <button
                        type="button"
                        :class="[
                            'flex h-8 w-8 items-center justify-center rounded-full text-xs font-black transition-all duration-300 sm:h-10 sm:w-10 sm:text-sm',
                            steps.length - 1 <= currentStepIndex
                                ? 'bg-[#d8c59f] text-gray-950'
                                : 'bg-gray-100 text-gray-400',
                        ]"
                        disabled
                    >
                        <svg
                            v-if="steps.length - 1 <= currentStepIndex"
                            class="h-4 w-4 sm:h-5 sm:w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="3"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4.5 12.75l6 6 9-13.5"
                            />
                        </svg>
                        <span v-else>{{ steps[steps.length - 1].icon }}</span>
                    </button>
                </div>

                <!-- Step labels -->
                <div class="mt-2 flex justify-center">
                    <span class="text-sm font-semibold text-gray-700">
                        Paso {{ currentStepIndex + 1 }} de
                        {{ steps.length }}
                        &mdash;
                        {{ steps[currentStepIndex]?.label }}
                    </span>
                </div>
            </div>

            <div
                v-if="wizard.errorMessage.value"
                class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700"
            >
                {{ wizard.errorMessage.value }}
            </div>

            <Transition name="step-fade" mode="out-in">
                <StepServices
                    v-if="wizard.step.value === 'services'"
                    :key="'services'"
                    :wizard="wizard"
                />

                <StepStaff
                    v-else-if="wizard.step.value === 'staff'"
                    :key="'staff'"
                    :wizard="wizard"
                    :barbers="barbers"
                />

                <StepSchedule
                    v-else-if="wizard.step.value === 'schedule'"
                    :key="'schedule'"
                    :wizard="wizard"
                />

                <StepCheckout
                    v-else-if="wizard.step.value === 'checkout'"
                    :key="'checkout'"
                    :wizard="wizard"
                    :shop="shop"
                />
            </Transition>
        </section>

        <BookingSummary
            v-if="wizard.step.value !== 'checkout' || !wizard.reservationCreated.value"
            :wizard="wizard"
        />
    </BookingLayout>
</template>

<style scoped>
.step-fade-enter-active,
.step-fade-leave-active {
    transition: all 0.2s ease-in-out;
}

.step-fade-enter-from {
    opacity: 0;
    transform: translateX(16px);
}

.step-fade-leave-to {
    opacity: 0;
    transform: translateX(-16px);
}
</style>
