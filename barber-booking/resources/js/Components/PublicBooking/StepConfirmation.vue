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
        <div
            v-if="wizard.successMessage.value"
            class="rounded-2xl border border-green-200 bg-green-50 p-6 text-green-800"
        >
            {{ wizard.successMessage.value }}
        </div>

        <div
            v-if="wizard.errorMessage.value"
            class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-6 text-red-700"
        >
            {{ wizard.errorMessage.value }}
        </div>

        <h2 class="mt-8 text-4xl font-black">Reserva creada</h2>

        <p class="mt-4 text-xl text-gray-600">
            Tu cita quedó pendiente de confirmación.
        </p>

        <div class="mt-8 rounded-2xl border border-gray-200 p-6">
            <p class="text-lg">
                <strong>Cliente:</strong>
                {{ wizard.createdReservation.value.customer_name }}
            </p>

            <p class="mt-3 text-lg">
                <strong>Servicio:</strong>
                {{ wizard.createdReservation.value.service.name }}
            </p>

            <p
                v-if="wizard.createdReservation.value.addon_service"
                class="mt-3 text-lg"
            >
                <strong>Extra:</strong>
                {{ wizard.createdReservation.value.addon_service.name }}
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
                    wizard.formatTime(wizard.createdReservation.value.end_time)
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

            <p class="mt-3 text-lg">
                <strong>Estado de pago:</strong>
                {{ wizard.createdReservation.value.payment_status }}
            </p>
        </div>

        <div class="mt-8 rounded-2xl border border-gray-200 p-6">
            <h3 class="text-2xl font-black">Subir comprobante de pago</h3>

            <p class="mt-3 text-gray-600">
                Sube una imagen del comprobante para que la barbería pueda
                revisar y confirmar tu cita.
            </p>

            <label
                class="mt-6 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center transition hover:border-gray-950"
            >
                <span class="text-lg font-black"> Seleccionar imagen </span>

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

            <div v-if="wizard.receiptPreview.value" class="mt-6">
                <p class="mb-3 font-black">Vista previa</p>

                <img
                    :src="wizard.receiptPreview.value"
                    alt="Comprobante seleccionado"
                    class="max-h-80 rounded-2xl border border-gray-200 object-contain"
                />
            </div>

            <div
                v-if="wizard.receiptUploaded.value"
                class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800"
            >
                Comprobante subido correctamente. Tu reserva está pendiente de
                revisión.
            </div>

            <button
                type="button"
                class="mt-6 w-full rounded-xl px-6 py-4 text-lg font-black transition"
                :class="
                    wizard.receiptFile.value && !wizard.uploadingReceipt.value
                        ? 'bg-[#d8c59f] text-gray-950 hover:bg-[#cdb88c]'
                        : 'bg-gray-100 text-gray-400'
                "
                :disabled="
                    !wizard.receiptFile.value || wizard.uploadingReceipt.value
                "
                @click="wizard.uploadReceipt"
            >
                {{
                    wizard.uploadingReceipt.value
                        ? "Subiendo comprobante..."
                        : "Subir comprobante"
                }}
            </button>

            <a
                v-if="wizard.receiptUrl.value"
                :href="wizard.receiptUrl.value"
                target="_blank"
                class="mt-4 inline-block font-black underline"
            >
                Ver comprobante subido
            </a>
        </div>

        <button
            type="button"
            class="mt-8 rounded-xl bg-gray-950 px-8 py-4 text-lg font-black text-white transition hover:bg-gray-800"
            @click="wizard.resetFlow"
        >
            Crear otra reserva
        </button>
    </div>
</template>
