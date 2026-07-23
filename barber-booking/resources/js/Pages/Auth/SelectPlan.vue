<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    paymentInfo: {
        type: Object,
        default: () => ({}),
    },
});

const period = ref('monthly');
const submitted = ref(false);

const form = useForm({
    plan_slug: '',
    billing_period: 'monthly',
    reference: '',
});

const priceForPeriod = (plan) =>
    period.value === 'annual' ? plan.annual_price : plan.monthly_price;

const selectPlan = (slug) => {
    form.plan_slug = slug;
    form.billing_period = period.value;
    form.post(route('register.plan.store'), {
        preserveScroll: true,
        onSuccess: () => {
            submitted.value = true;
            form.reset('plan_slug');
        },
    });
};

const formatPrice = (price) => `$${parseFloat(price).toFixed(2)}`;
</script>

<template>
    <GuestLayout>
        <Head title="Elegí tu plan" />

        <div class="max-w-4xl mx-auto py-12 px-4">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Elegí tu plan</h1>
                <p v-if="barberShop" class="mt-2 text-gray-600">{{ barberShop.name }}</p>
                <p v-if="daysRemaining !== null && daysRemaining > 0" class="mt-4 text-sm text-amber-600 bg-amber-50 rounded-lg px-4 py-2 inline-block">
                    Te quedan {{ daysRemaining }} día{{ daysRemaining !== 1 ? 's' : '' }} de prueba gratis
                </p>
                <p v-else class="mt-4 text-sm text-red-600 bg-red-50 rounded-lg px-4 py-2 inline-block">
                    Tu período de prueba terminó. Elegí un plan y registrá tu pago para continuar.
                </p>
            </div>

            <!-- Confirmación de pago registrado -->
            <div v-if="submitted" class="mb-8 rounded-xl border border-green-200 bg-green-50 p-4 text-center text-green-800">
                ✅ Registramos tu pago. En cuanto lo confirmemos, se activa tu plan. ¡Gracias!
            </div>

            <!-- Toggle período -->
            <div class="mb-8 flex justify-center">
                <div class="inline-flex rounded-xl bg-gray-100 p-1">
                    <button
                        type="button"
                        class="rounded-lg px-5 py-2 text-sm font-semibold transition"
                        :class="period === 'monthly' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'"
                        @click="period = 'monthly'"
                    >
                        Mensual
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-5 py-2 text-sm font-semibold transition"
                        :class="period === 'annual' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'"
                        @click="period = 'annual'"
                    >
                        Anual <span class="text-green-600">· 2 meses gratis</span>
                    </button>
                </div>
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
                    <div v-if="plan.slug === 'profesional'" class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="bg-amber-500 text-white text-xs font-semibold px-4 py-1 rounded-full">Recomendado</span>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900">{{ plan.name }}</h3>
                        <p class="mt-2 text-sm text-gray-500">{{ plan.description }}</p>

                        <div class="mt-6">
                            <span class="text-4xl font-bold text-gray-900">{{ formatPrice(priceForPeriod(plan)) }}</span>
                            <span class="text-gray-500 ml-1">/{{ period === 'annual' ? 'año' : 'mes' }}</span>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">+ {{ formatPrice(plan.setup_price) }} setup único</div>

                        <ul class="mt-6 space-y-3">
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Hasta {{ plan.max_barbers }} barberos
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Reservas online + panel
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Caja, comisiones y reportes
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        <PrimaryButton
                            class="w-full justify-center"
                            :class="{ 'opacity-25': form.processing && form.plan_slug === plan.slug }"
                            :disabled="form.processing"
                            @click="selectPlan(plan.slug)"
                        >
                            <template v-if="form.processing && form.plan_slug === plan.slug">Registrando...</template>
                            <template v-else>Pagar {{ plan.name }}</template>
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Datos de pago de la plataforma -->
            <div class="mt-10 rounded-2xl border border-gray-200 bg-gray-50 p-6">
                <h4 class="text-base font-semibold text-gray-900">Cómo pagar</h4>
                <p class="mt-1 text-sm text-gray-500">
                    Transferí al siguiente detalle y elegí tu plan arriba. Confirmamos tu pago y activamos tu cuenta.
                </p>
                <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                    <div><dt class="inline font-medium text-gray-700">Banco:</dt> <dd class="inline text-gray-600">{{ paymentInfo.bank }}</dd></div>
                    <div><dt class="inline font-medium text-gray-700">Cuenta ({{ paymentInfo.account_type }}):</dt> <dd class="inline text-gray-600">{{ paymentInfo.account }}</dd></div>
                    <div><dt class="inline font-medium text-gray-700">Titular:</dt> <dd class="inline text-gray-600">{{ paymentInfo.account_owner }}</dd></div>
                    <div v-if="paymentInfo.whatsapp"><dt class="inline font-medium text-gray-700">WhatsApp:</dt> <dd class="inline text-gray-600">{{ paymentInfo.whatsapp }}</dd></div>
                </dl>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nº de comprobante / referencia (opcional)</label>
                    <input
                        v-model="form.reference"
                        type="text"
                        class="w-full rounded-lg border-gray-300 text-sm"
                        placeholder="Ej: 001234567"
                    />
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
