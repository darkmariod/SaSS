<script setup>
import { computed } from "vue";

const props = defineProps({
    wizard: { type: Object, required: true },
    shop: { type: Object, required: true },
});

const paymentQrUrl = computed(() => {
    if (!props.shop.bank_qr_image) return null;
    return `/storage/${props.shop.bank_qr_image}`;
});
</script>

<template>
    <Transition name="fade" mode="out-in">
        <!-- ════════════════════════════════════════════
             MODE 1 — Contact Form
             ════════════════════════════════════════════ -->
        <div v-if="!wizard.reservationCreated.value" key="form">
            <button
                type="button"
                class="mb-8 inline-flex items-center gap-2 text-gray-500 transition hover:text-gray-950"
                @click="wizard.goToSchedule"
            >
                ← Fecha y hora
            </button>

            <div
                v-if="wizard.errorMessage.value"
                class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-700"
            >
                {{ wizard.errorMessage.value }}
            </div>

            <div
                class="mb-8 flex items-start justify-between gap-4 sm:mb-10"
            >
                <div>
                    <h1
                        class="text-2xl font-black tracking-tight text-gray-950 sm:text-4xl"
                    >
                        Información de contacto
                    </h1>
                    <p
                        class="mt-2 text-base text-gray-500 sm:mt-3 sm:text-lg"
                    >
                        Completá tus datos para crear la reserva.
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
                <!-- Phone -->
                <div>
                    <label
                        class="mb-3 block text-sm font-black text-gray-950"
                    >
                        Número de teléfono
                    </label>
                    <div
                        class="flex overflow-hidden rounded-xl border border-gray-300 bg-white shadow-sm focus-within:border-gray-950"
                    >
                        <div
                            class="flex items-center gap-2 border-r border-gray-200 bg-gray-50 px-3 py-3 text-sm font-black sm:px-4 sm:py-4"
                        >
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
                        Al proporcionar tu número de teléfono, aceptás recibir
                        mensajes informativos ocasionales sobre tu reserva.
                    </p>
                </div>

                <!-- Name + Last name -->
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

                <!-- Email -->
                <input
                    v-model="wizard.customer.value.email"
                    type="email"
                    placeholder="Correo electrónico"
                    class="min-h-[44px] w-full rounded-xl border border-gray-300 px-4 py-3 text-base shadow-sm outline-none transition focus:border-gray-950 sm:px-5 sm:py-4 sm:text-lg"
                />

                <!-- Payment method -->
                <div class="border-t border-gray-200 pt-8">
                    <h2
                        class="text-xl font-black text-gray-950 sm:text-2xl"
                    >
                        Método de pago
                    </h2>

                    <div
                        class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:mt-6 sm:p-6"
                    >
                        <div class="space-y-4">
                            <!-- At appointment -->
                            <label
                                class="flex cursor-pointer items-start gap-4 rounded-xl border border-gray-200 p-4 transition hover:border-gray-950 has-[:checked]:border-gray-950 has-[:checked]:bg-gray-50"
                            >
                                <input
                                    type="radio"
                                    name="paymentOption"
                                    value="at_appointment"
                                    :checked="
                                        wizard.paymentOption.value ===
                                        'at_appointment'
                                    "
                                    @change="
                                        wizard.paymentOption.value =
                                            'at_appointment'
                                    "
                                    class="mt-1 h-4 w-4 shrink-0 accent-gray-950"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">💵</span>
                                        <h3
                                            class="text-base font-black text-gray-950 sm:text-lg"
                                        >
                                            Pagar en la cita
                                        </h3>
                                    </div>
                                    <p
                                        class="mt-1 text-sm text-gray-500 sm:text-base"
                                    >
                                        Pagás en efectivo o con tarjeta
                                        directamente en la barbería.
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p
                                        class="text-sm font-black text-gray-950"
                                    >
                                        USD
                                        {{
                                            Number(
                                                wizard.total.value,
                                            ).toFixed(2)
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        en la cita
                                    </p>
                                </div>
                            </label>

                            <!-- Pay today — bank transfer -->
                            <label
                                class="flex cursor-pointer items-start gap-4 rounded-xl border border-gray-200 p-4 transition hover:border-gray-950 has-[:checked]:border-gray-950 has-[:checked]:bg-gray-50"
                            >
                                <input
                                    type="radio"
                                    name="paymentOption"
                                    value="today"
                                    :checked="
                                        wizard.paymentOption.value === 'today'
                                    "
                                    @change="
                                        wizard.paymentOption.value = 'today'
                                    "
                                    class="mt-1 h-4 w-4 shrink-0 accent-gray-950"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🏦</span>
                                        <h3
                                            class="text-base font-black text-gray-950 sm:text-lg"
                                        >
                                            Pagar hoy — Transferencia
                                        </h3>
                                    </div>
                                    <p
                                        class="mt-1 text-sm text-gray-500 sm:text-base"
                                    >
                                        Transferís el total ahora y la cita se
                                        confirma más rápido.
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p
                                        class="text-sm font-black text-gray-950"
                                    >
                                        USD
                                        {{
                                            Number(
                                                wizard.total.value,
                                            ).toFixed(2)
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-500">hoy</p>
                                </div>
                            </label>
                        </div>

                        <!-- Bank details (when pay today + bank info exists) -->
                        <div
                            v-if="
                                wizard.payToday.value &&
                                (shop.bank_name ||
                                    shop.bank_account ||
                                    shop.bank_account_owner ||
                                    shop.payment_instructions ||
                                    shop.bank_qr_image)
                            "
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
                                <p v-if="shop.bank_account_owner">
                                    <strong>Titular:</strong>
                                    {{ shop.bank_account_owner }}
                                </p>
                                <p
                                    v-if="shop.payment_instructions"
                                    class="pt-3 leading-6 text-gray-600"
                                >
                                    {{ shop.payment_instructions }}
                                </p>
                            </div>

                            <div
                                v-if="paymentQrUrl"
                                class="flex flex-col items-center justify-center"
                            >
                                <img
                                    :src="paymentQrUrl"
                                    alt="QR de pago"
                                    class="h-40 w-40 rounded-xl border border-gray-200 bg-white object-contain p-2"
                                />
                                <p
                                    class="mt-2 text-center text-xs font-black uppercase tracking-wide text-gray-500"
                                >
                                    QR de pago
                                </p>
                            </div>
                        </div>

                        <!-- Fallback when pay today but no bank info -->
                        <div
                            v-if="
                                wizard.payToday.value &&
                                !(
                                    shop.bank_name ||
                                    shop.bank_account ||
                                    shop.bank_account_owner ||
                                    shop.payment_instructions ||
                                    shop.bank_qr_image
                                )
                            "
                            class="mt-6 rounded-2xl bg-yellow-50 p-5 text-yellow-800"
                        >
                            Esta barbería todavía no configuró datos bancarios.
                            Podés crear la reserva y coordinar el pago
                            directamente con el negocio.
                        </div>
                    </div>
                </div>

                <!-- Info text -->
                <div
                    class="rounded-2xl bg-gray-50 p-4 text-sm text-gray-500 sm:p-5 sm:text-base"
                >
                    <template v-if="wizard.payToday.value">
                        Después de reservar, vas a poder subir el comprobante
                        de transferencia para que la barbería revise y confirme
                        tu cita.
                    </template>
                    <template v-else>
                        No necesitás hacer ningún pago ahora. Abonás el total
                        al llegar a la barbería.
                    </template>
                </div>

                <!-- Submit button -->
                <button
                    type="button"
                    class="w-full rounded-xl px-6 py-4 text-lg font-black transition md:hidden"
                    :class="
                        wizard.creatingReservation.value
                            ? 'bg-gray-100 text-gray-400'
                            : 'bg-[#d8c59f] text-gray-950 hover:bg-[#cdb88c]'
                    "
                    :disabled="wizard.creatingReservation.value"
                    @click="wizard.createReservation"
                >
                    {{
                        wizard.creatingReservation.value
                            ? "Reservando..."
                            : "Reservar cita"
                    }}
                </button>
            </div>
        </div>

        <!-- ════════════════════════════════════════════
             MODE 2 — Confirmation
             ════════════════════════════════════════════ -->
        <div v-else key="confirmation">
            <div
                v-if="wizard.errorMessage.value"
                class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-700"
            >
                {{ wizard.errorMessage.value }}
            </div>

            <!-- Success indicator -->
            <div class="mb-8 text-center sm:mb-10">
                <div
                    class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100 sm:h-24 sm:w-24"
                >
                    <svg
                        class="h-10 w-10 text-green-600 sm:h-12 sm:w-12"
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
                </div>
                <h1
                    class="text-3xl font-black tracking-tight text-gray-950 sm:text-4xl"
                >
                    Reserva creada
                </h1>
                <p class="mt-2 text-lg text-gray-500 sm:mt-3 sm:text-xl">
                    Tu cita quedó pendiente de confirmación.
                </p>
            </div>

            <!-- Reservation summary -->
            <div class="rounded-2xl border border-gray-200 p-6">
                <p class="text-lg" v-if="wizard.createdReservation.value.customer_name">
                    <strong>Cliente:</strong>
                    {{ wizard.createdReservation.value.customer_name }}
                </p>

                <p class="mt-3 text-lg">
                    <strong>Servicio:</strong>
                    {{ wizard.createdReservation.value.service?.name }}
                </p>

                <p
                    v-if="
                        wizard.createdReservation.value.staff?.name ||
                        wizard.selectedBarberName.value
                    "
                    class="mt-3 text-lg"
                >
                    <strong>Barbero:</strong>
                    {{
                        wizard.createdReservation.value.staff?.name ||
                        wizard.selectedBarberName.value
                    }}
                </p>

                <p
                    v-if="wizard.createdReservation.value.addon_service"
                    class="mt-3 text-lg"
                >
                    <strong>Extra:</strong>
                    {{ wizard.createdReservation.value.addon_service?.name }}
                </p>

                <p class="mt-3 text-lg">
                    <strong>Fecha:</strong>
                    {{
                        wizard.formatDate(
                            wizard.createdReservation.value.reservation_date?.substring(
                                0,
                                10,
                            ),
                        )
                    }}
                </p>

                <p class="mt-3 text-lg">
                    <strong>Horario:</strong>
                    {{
                        wizard.formatTime(
                            wizard.createdReservation.value.start_time,
                        )
                    }}
                    -
                    {{
                        wizard.formatTime(
                            wizard.createdReservation.value.end_time,
                        )
                    }}
                </p>

                <p class="mt-3 text-lg">
                    <strong>Total:</strong>
                    USD
                    {{
                        Number(
                            wizard.createdReservation.value.total_amount,
                        ).toFixed(2)
                    }}
                </p>
            </div>

            <!-- Receipt upload (only if payment was "today") -->
            <div
                v-if="wizard.payToday.value"
                class="mt-8 rounded-2xl border border-gray-200 p-6"
            >
                <h3 class="text-2xl font-black">
                    Subir comprobante de pago
                </h3>

                <p class="mt-3 text-gray-600">
                    Subí una imagen del comprobante para que la barbería pueda
                    revisar y confirmar tu cita.
                </p>

                <!-- After upload: success state -->
                <div v-if="wizard.receiptUploaded.value" class="mt-6">
                    <div
                        class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-5 text-green-800"
                    >
                        <svg
                            class="h-6 w-6 shrink-0 text-green-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4.5 12.75l6 6 9-13.5"
                            />
                        </svg>
                        <span class="font-black">Comprobante subido</span>
                    </div>

                    <a
                        v-if="wizard.receiptUrl?.value"
                        :href="wizard.receiptUrl.value"
                        target="_blank"
                        class="mt-4 inline-flex items-center gap-1 font-black text-gray-950 underline transition hover:text-gray-600"
                    >
                        Ver comprobante subido
                    </a>
                </div>

                <!-- Before upload: file picker + preview + button -->
                <template v-else>
                    <label
                        class="mt-6 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center transition hover:border-gray-950"
                    >
                        <span class="text-lg font-black">
                            Seleccionar imagen
                        </span>
                        <span class="mt-2 text-sm text-gray-600">
                            JPG, PNG o WEBP. Máximo 4MB.
                        </span>
                        <input
                            type="file"
                            accept="image/png,image/jpeg,image/jpg,image/webp"
                            class="hidden"
                            @change="wizard.setReceiptFile"
                        />
                    </label>

                    <div
                        v-if="wizard.receiptPreview.value"
                        class="mt-6"
                    >
                        <p class="mb-3 font-black">Vista previa</p>
                        <img
                            :src="wizard.receiptPreview.value"
                            alt="Comprobante seleccionado"
                            class="max-h-80 rounded-2xl border border-gray-200 object-contain"
                        />
                    </div>

                    <button
                        type="button"
                        class="mt-6 w-full rounded-xl px-6 py-4 text-lg font-black transition"
                        :class="
                            wizard.receiptFile.value &&
                            !wizard.uploadingReceipt.value
                                ? 'bg-[#d8c59f] text-gray-950 hover:bg-[#cdb88c]'
                                : 'bg-gray-100 text-gray-400'
                        "
                        :disabled="
                            !wizard.receiptFile.value ||
                            wizard.uploadingReceipt.value
                        "
                        @click="wizard.uploadReceipt"
                    >
                        {{
                            wizard.uploadingReceipt.value
                                ? "Subiendo comprobante..."
                                : "Subir comprobante"
                        }}
                    </button>
                </template>
            </div>

            <!-- Create another reservation -->
            <button
                type="button"
                class="mt-8 w-full rounded-xl bg-gray-950 px-8 py-4 text-lg font-black text-white transition hover:bg-gray-800"
                @click="wizard.resetFlow"
            >
                Crear otra reserva
            </button>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
