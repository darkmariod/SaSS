<script setup>
import { computed } from "vue";

const props = defineProps({
    wizard: {
        type: Object,
        required: true,
    },
});

const toIsoLocal = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

const dateOptions = computed(() => {
    return Array.from({ length: 7 }).map((_, index) => {
        const date = new Date();
        date.setDate(date.getDate() + index);

        return {
            iso: toIsoLocal(date),
            weekday: date
                .toLocaleDateString("es-EC", { weekday: "short" })
                .replace(".", ""),
            day: date.getDate(),
        };
    });
});

const selectDate = async (iso) => {
    props.wizard.selectedDate.value = iso;
    await props.wizard.loadAvailability();
};
</script>

<template>
    <div>
        <button
            type="button"
            class="mb-6 inline-flex items-center gap-2 text-base font-black text-gray-600 hover:text-gray-950 sm:mb-8 sm:text-lg"
            @click="wizard.step.value = 'staff'"
        >
            ← Personal
        </button>

        <h2 class="text-2xl font-black tracking-tight sm:text-4xl">Fecha y hora</h2>

        <div class="mt-6 sm:mt-8">
            <div class="flex items-center gap-3 overflow-x-auto pb-2 sm:gap-4">
                 <button
                     v-for="date in dateOptions"
                     :key="date.iso"
                     type="button"
                     class="flex min-h-[44px] min-w-16 flex-col items-center rounded-xl border px-3 py-3 text-center transition sm:min-w-20 sm:px-5 sm:py-4"
                     :class="
                        wizard.selectedDate.value === date.iso
                            ? 'border-gray-950 bg-gray-950 text-white'
                            : 'border-gray-200 bg-white hover:border-gray-950'
                    "
                    @click="selectDate(date.iso)"
                >
                    <span class="text-xs font-bold uppercase sm:text-sm">
                        {{ date.weekday }}
                    </span>

                    <span class="mt-1 text-xl font-black sm:text-2xl">
                        {{ date.day }}
                    </span>
                </button>
            </div>

            <div class="mt-4 flex max-w-sm items-center gap-3 sm:mt-5">
                <span class="text-sm font-semibold text-gray-600">
                    Otra fecha:
                </span>

                  <input
                      type="date"
                      :value="wizard.selectedDate.value"
                      @change="selectDate($event.target.value)"
                      class="min-h-[44px] rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:border-gray-950 sm:px-4"
                  />
            </div>
        </div>

        <div class="mt-8 sm:mt-10">
            <p v-if="wizard.loadingSlots.value" class="text-gray-600">
                Consultando horarios disponibles...
            </p>

            <div
                v-else-if="
                    wizard.selectedDate.value && wizard.slots.value.length === 0
                "
                class="rounded-xl bg-gray-50 p-5 text-gray-600 sm:p-6"
            >
                No hay horarios disponibles para esta fecha.
            </div>

            <div v-else-if="wizard.selectedDate.value">
                <p class="text-sm text-gray-600">
                    Las horas se muestran en horario local.
                </p>

                <h3 class="mt-6 text-xl font-black sm:mt-8 sm:text-2xl">
                    {{ wizard.formatDate(wizard.selectedDate.value) }}
                </h3>

                <div v-if="wizard.slotsMorning.value.length" class="mt-6 sm:mt-8">
                    <h4 class="text-base font-black sm:text-lg">Mañana</h4>

                    <div class="mt-3 grid grid-cols-2 gap-2 sm:mt-4 sm:grid-cols-2 sm:gap-3 lg:grid-cols-3">
                         <button
                             v-for="slot in wizard.slotsMorning.value"
                             :key="slot.start_time"
                             type="button"
                             class="min-h-[44px] rounded-xl px-4 py-3 text-base font-black transition sm:px-6 sm:py-4 sm:text-lg"
                             :class="
                                wizard.selectedSlot.value?.start_time ===
                                slot.start_time
                                    ? 'bg-gray-950 text-white'
                                    : 'bg-gray-100 text-gray-950 hover:bg-gray-200'
                            "
                            @click="wizard.selectedSlot.value = slot"
                        >
                            {{ wizard.formatTime(slot.start_time) }}
                        </button>
                    </div>
                </div>

                <div v-if="wizard.slotsAfternoon.value.length" class="mt-6 sm:mt-8">
                    <h4 class="text-base font-black sm:text-lg">Tarde</h4>

                    <div class="mt-3 grid grid-cols-2 gap-2 sm:mt-4 sm:grid-cols-2 sm:gap-3 lg:grid-cols-3">
                         <button
                             v-for="slot in wizard.slotsAfternoon.value"
                             :key="slot.start_time"
                             type="button"
                             class="min-h-[44px] rounded-xl px-4 py-3 text-base font-black transition sm:px-6 sm:py-4 sm:text-lg"
                             :class="
                                wizard.selectedSlot.value?.start_time ===
                                slot.start_time
                                    ? 'bg-gray-950 text-white'
                                    : 'bg-gray-100 text-gray-950 hover:bg-gray-200'
                            "
                            @click="wizard.selectedSlot.value = slot"
                        >
                            {{ wizard.formatTime(slot.start_time) }}
                        </button>
                    </div>
                </div>

                <div v-if="wizard.slotsNight.value.length" class="mt-6 sm:mt-8">
                    <h4 class="text-base font-black sm:text-lg">Noche</h4>

                    <div class="mt-3 grid grid-cols-2 gap-2 sm:mt-4 sm:grid-cols-2 sm:gap-3 lg:grid-cols-3">
                         <button
                             v-for="slot in wizard.slotsNight.value"
                             :key="slot.start_time"
                             type="button"
                             class="min-h-[44px] rounded-xl px-4 py-3 text-base font-black transition sm:px-6 sm:py-4 sm:text-lg"
                             :class="
                                wizard.selectedSlot.value?.start_time ===
                                slot.start_time
                                    ? 'bg-gray-950 text-white'
                                    : 'bg-gray-100 text-gray-950 hover:bg-gray-200'
                            "
                            @click="wizard.selectedSlot.value = slot"
                        >
                            {{ wizard.formatTime(slot.start_time) }}
                        </button>
                    </div>
                </div>

                <div
                    class="mt-10 rounded-xl bg-gray-50 p-5 text-sm text-gray-600"
                >
                    ¿No encuentras tu horario preferido? Más adelante
                    agregaremos lista de espera.
                </div>
            </div>
        </div>
    </div>
</template>
