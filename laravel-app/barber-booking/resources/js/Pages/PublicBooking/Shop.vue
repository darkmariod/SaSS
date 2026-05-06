<script setup>
import { Head } from "@inertiajs/vue3";
import { useBookingWizard } from "@/Composables/useBookingWizard";

import BookingLayout from "@/Components/PublicBooking/BookingLayout.vue";
import BookingSummary from "@/Components/PublicBooking/BookingSummary.vue";

import StepServices from "@/Components/PublicBooking/StepServices.vue";
import StepServiceOptions from "@/Components/PublicBooking/StepServiceOptions.vue";
import StepStaff from "@/Components/PublicBooking/StepStaff.vue";
import StepSchedule from "@/Components/PublicBooking/StepSchedule.vue";
import StepContact from "@/Components/PublicBooking/StepContact.vue";
import StepConfirmation from "@/Components/PublicBooking/StepConfirmation.vue";

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
});

const wizard = useBookingWizard(props);
</script>

<template>
    <Head :title="shop.name" />

    <BookingLayout :shop="shop">
        <section>
            <div
                v-if="wizard.errorMessage.value"
                class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700"
            >
                {{ wizard.errorMessage.value }}
            </div>

            <StepServices
                v-if="wizard.step.value === 'services'"
                :wizard="wizard"
            />

            <StepServiceOptions
                v-if="wizard.step.value === 'options'"
                :wizard="wizard"
            />

            <StepStaff
                v-if="wizard.step.value === 'staff'"
                :wizard="wizard"
                :barbers="barbers"
            />

            <StepSchedule
                v-if="wizard.step.value === 'schedule'"
                :wizard="wizard"
            />

            <StepContact
                v-if="wizard.step.value === 'contact'"
                :wizard="wizard"
                :shop="shop"
            />

            <StepConfirmation
                v-if="wizard.step.value === 'confirmation'"
                :wizard="wizard"
            />
        </section>

        <BookingSummary
            v-if="wizard.step.value !== 'confirmation'"
            :wizard="wizard"
        />
    </BookingLayout>
</template>
