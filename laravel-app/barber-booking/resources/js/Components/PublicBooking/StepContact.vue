<script setup>
defineProps({
    wizard: {
        type: Object,
        required: true,
    },
    shop: {
        type: Object,
        required: true,
    },
});

const paymentQrUrl = (shop) => {
    if (!shop.payment_qr) {
        return null;
    }
    return `/storage/${shop.payment_qr}`;
};
</script>

<template>
    <div>
        <button
            type="button"
            class="mb-8 inline-flex items-center gap-2 text-gray-500 transition hover:text-gray-950"
            @click="wizard.goToSchedule"
        >
            ← Fecha y hora
        </button>

        <div class="mb-8 flex items-start justify-between gap-4 sm:mb-10">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-950 sm:text-4xl">
                    Información de contacto
                </h1>
                <p class="mt-2 text-base text-gray-500 sm:mt-3 sm:text-lg">
                    Completa tus datos para crear la reserva.
                </p>
            </div>
            <button
                type="button"
                class="hidden text-sm font-black underline md:inline-block"
            >
                Iniciar sesión
            </button>
        </div>

        <div class="space-y-8">
            <div>
                <label class="mb-3 block text-sm font-black text-gray-950">
                    Número de teléfono
                </label>
                <div class="flex overflow-hidden rounded-xl border border-gray-300 bg-white shadow-sm focus-within:border-gray-950">
                    <div class="flex items-center gap-2 border-r border-gray-200 bg-gray-50 px-3 py-3 text-sm font-black sm:px-4 sm:py-4">
                        <span>🇪🇨</span>
                        <span>+593</span>
                    </div>
                 <input
                         v-model="wizard.customer.value.phone"
                         type="tel"
                         inputmode="numeric"
                         placeholder="987621566"
                         class="min-h-[44px] w-full border-0 px-4 py-3 text-base outline-none ring-0 focus:ring-0 sm:px-5 sm:py-4 sm:text-lg"
                     />
                </div>
                <p class="mt-3 text-sm leading-6 text-gray-500">
                    Al proporcionar tu número de teléfono, aceptas recibir mensajes informativos ocasionales sobre tu reserva.
                </p>
            </div>

            <div class="grid gap-3 sm:gap-4 md:grid-cols-2">
                 <input
                     v-model="wizard.customer.value.name"
                     type="text"
                     placeholder="Nombre"
                     class="min-h-[44px] rounded-xl border border-gray-300 px-4 py-3 text-base shadow-sm outline-none transition focus:border-gray-950 sm:px-5 sm:py-4 sm:text-lg"
                 />
                 <input
                     v-model="wizard.customer.value.lastName"
                     type="text"
                     placeholder="Apellido"
                     class="min-h-[44px] rounded-xl border border-gray-300 px-4 py-3 text-base shadow-sm outline-none transition focus:border-gray-950 sm:px-5 sm:py-4 sm:text-lg"
                 />
            </div>

                 <input
                 v-model="wizard.customer.value.email"
                 type="email"
                 placeholder="Correo electrónico"
                 class="min-h-[44px] w-full rounded-xl border border-gray-300 px-4 py-3 text-base shadow-sm outline-none transition focus:border-gray-950 sm:px-5 sm:py-4 sm:text-lg"
             />

            <div class="border-t border-gray-200 pt-8">
                <h2 class="text-xl font-black text-gray-950 sm:text-2xl">
                    Método de pago
                </h2>

                <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:mt-6 sm:p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#f4efe7] text-lg sm:h-12 sm:w-12 sm:text-xl">
                            🏦
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-black text-gray-950 sm:text-lg">
                                Pago por transferencia
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 sm:text-base">
                                La cita quedará pendiente hasta que la barbería apruebe el comprobante.
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="shop.bank_name || shop.bank_account || shop.bank_account_holder || shop.payment_instructions || shop.payment_qr"
                        class="mt-6 grid gap-6 rounded-2xl bg-[#f4efe7] p-6 md:grid-cols-[1fr_auto]"
                    >
                        <div class="space-y-2 text-gray-800">
                            <p v-if="shop.bank_name">
                                <strong>Banco:</strong>
                                {{ shop.bank_name }}
                            </p>
                            <p v-if="shop.bank_account">
                                <strong>Cuenta:</strong>
                                {{ shop.bank_account }}
                            </p>
                            <p v-if="shop.bank_account_holder">
                                <strong>Titular:</strong>
                                {{ shop.bank_account_holder }}
                            </p>
                            <p
                                v-if="shop.payment_instructions"
                                class="pt-3 leading-6 text-gray-600"
                            >
                                {{ shop.payment_instructions }}
                            </p>
                        </div>

                        <div
                            v-if="paymentQrUrl(shop)"
                            class="flex flex-col items-center justify-center"
                        >
                            <img
                                :src="paymentQrUrl(shop)"
                                alt="QR de pago"
                                class="h-40 w-40 rounded-xl border border-gray-200 bg-white object-contain p-2"
                            />
                            <p class="mt-2 text-center text-xs font-black uppercase tracking-wide text-gray-500">
                                QR de pago
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mt-6 rounded-2xl bg-yellow-50 p-5 text-yellow-800"
                    >
                        Esta barbería todavía no configuró datos bancarios. Puedes crear la reserva y coordinar el pago directamente con el negocio.
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-gray-50 p-4 text-sm text-gray-500 sm:p-5 sm:text-base">
                Después de reservar, podrás subir el comprobante de pago para que la barbería revise y confirme tu cita.
            </div>

            <button
                type="button"
                class="w-full rounded-xl px-6 py-4 text-lg font-black transition md:hidden"
                :class="wizard.creatingReservation.value ? 'bg-gray-100 text-gray-400' : 'bg-[#d8c59f] text-gray-950 hover:bg-[#cdb88c]'"
                :disabled="wizard.creatingReservation.value"
                @click="wizard.createReservation"
            >
                {{ wizard.creatingReservation.value ? 'Reservando...' : 'Reservar cita' }}
            </button>
        </div>
    </div>
</template>
