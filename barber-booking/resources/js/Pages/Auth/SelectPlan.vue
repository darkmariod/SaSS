<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    plans: {
        type: Array,
        required: true,
    },
    currentSubscription: {
        type: Object,
        default: null,
    },
    daysRemaining: {
        type: Number,
        default: null,
    },
    barberShop: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    plan_slug: '',
});

const selectPlan = (slug) => {
    form.plan_slug = slug;
    form.post(route('register.plan.store'), {
        preserveScroll: true,
    });
};

const formatPrice = (price) => {
    return `$${parseFloat(price).toFixed(2)}`;
};
</script>

<template>
    <GuestLayout>
        <Head title="Elegí tu plan" />

        <div class="max-w-4xl mx-auto py-12 px-4">
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900">
                    Elegí tu plan
                </h1>
                <p v-if="barberShop" class="mt-2 text-gray-600">
                    {{ barberShop.name }}
                </p>
                <p v-if="daysRemaining !== null && daysRemaining > 0" class="mt-4 text-sm text-amber-600 bg-amber-50 rounded-lg px-4 py-2 inline-block">
                    Te quedan {{ daysRemaining }} día{{ daysRemaining !== 1 ? 's' : '' }} de prueba gratis
                </p>
                <p v-if="daysRemaining !== null && daysRemaining <= 0" class="mt-4 text-sm text-red-600 bg-red-50 rounded-lg px-4 py-2 inline-block">
                    Tu período de prueba terminó. Elegí un plan para continuar.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="relative flex flex-col rounded-2xl border-2 p-6 transition-all duration-200"
                    :class="form.plan_slug === plan.slug
                        ? 'border-amber-500 ring-2 ring-amber-200 shadow-lg'
                        : 'border-gray-200 hover:border-amber-300 hover:shadow-md'"
                >
                    <!-- Popular badge -->
                    <div v-if="plan.slug === 'profesional'" class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="bg-amber-500 text-white text-xs font-semibold px-4 py-1 rounded-full">
                            Recomendado
                        </span>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900">
                            {{ plan.name }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ plan.description }}
                        </p>

                        <div class="mt-6">
                            <span class="text-4xl font-bold text-gray-900">
                                {{ formatPrice(plan.monthly_price) }}
                            </span>
                            <span class="text-gray-500 ml-1">/mes</span>
                        </div>

                        <div class="mt-2 text-sm text-gray-500">
                            + {{ formatPrice(plan.setup_price) }} setup único
                        </div>

                        <ul class="mt-6 space-y-3">
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Hasta {{ plan.max_barbers }} barberos
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ plan.trial_days }} días de prueba gratis
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Reservas online
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Panel de administración
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Google Calendar sync
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        <PrimaryButton
                            class="w-full justify-center"
                            :class="{
                                'opacity-25': form.processing && form.plan_slug === plan.slug
                            }"
                            :disabled="form.processing"
                            @click="selectPlan(plan.slug)"
                        >
                            <template v-if="form.processing && form.plan_slug === plan.slug">
                                Activando...
                            </template>
                            <template v-else>
                                Elegir {{ plan.name }}
                            </template>
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
