<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    wizard: {
        type: Object,
        required: true,
    },
});

const categories = computed(() => {
    const unique = [
        ...new Set(
            props.wizard.mainServices.value.map(
                (service) => service.category || "General",
            ),
        ),
    ];

    if (unique.length === 0) {
        return ["General"];
    }

    return unique;
});

const activeCategory = ref(categories.value[0] || "General");

const filteredServices = computed(() => {
    return props.wizard.mainServices.value.filter((service) => {
        return (service.category || "General") === activeCategory.value;
    });
});
</script>

<template>
    <div>
        <h2 class="text-2xl font-black tracking-tight sm:text-4xl">Servicios</h2>

        <div
            class="mt-5 flex gap-5 overflow-x-auto border-b border-gray-200 text-sm font-black text-gray-600 sm:mt-7 sm:gap-7 sm:text-base"
        >
            <button
                v-for="category in categories"
                :key="category"
                type="button"
                class="whitespace-nowrap border-b-2 pb-3 transition"
                :class="
                    activeCategory === category
                        ? 'border-gray-950 text-gray-950'
                        : 'border-transparent hover:text-gray-950'
                "
                @click="activeCategory = category"
            >
                {{ category }}
            </button>
        </div>

        <div class="mt-6 sm:mt-9">
            <h3 class="text-2xl font-black">
                {{ activeCategory }}
            </h3>

            <div class="mt-4 divide-y divide-gray-200">
                 <button
                     v-for="service in filteredServices"
                     :key="service.id"
                     type="button"
                     class="group flex min-h-[60px] w-full items-center justify-between gap-4 py-4 text-left transition hover:bg-gray-50 sm:gap-6 sm:py-6"
                     @click="wizard.selectMainService(service)"
                 >
                    <div>
                        <h4
                            class="text-lg font-black tracking-tight group-hover:underline sm:text-xl"
                        >
                            {{ service.name }}
                        </h4>

                        <p
                            class="mt-1 max-w-xl text-sm leading-relaxed text-gray-600 sm:mt-2 sm:text-base"
                        >
                            {{
                                service.description ||
                                "Servicio profesional de barbería."
                            }}
                        </p>

                        <p class="mt-2 text-sm text-gray-700 sm:mt-3 sm:text-base">
                            <span v-if="Number(service.price) > 0">
                                USD {{ Number(service.price).toFixed(2) }}
                            </span>

                            <span v-else> El precio varía </span>

                            <span class="mx-2">·</span>
                            {{ service.duration_minutes }} min+
                        </p>

                        <p
                            v-if="
                                wizard.selectedMainService.value?.id ===
                                service.id
                            "
                            class="mt-3 text-sm font-black text-green-700"
                        >
                            ✓ Añadido
                        </p>
                    </div>

                         <div
                         class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-4 transition sm:h-12 sm:w-12"
                         :class="
                            wizard.selectedMainService.value?.id === service.id
                                ? 'border-gray-950 bg-gray-950'
                                : 'border-gray-300 group-hover:border-gray-950'
                        "
                    >
                        <div
                            v-if="
                                wizard.selectedMainService.value?.id ===
                                service.id
                            "
                            class="h-2.5 w-2.5 rounded-full bg-white"
                        />
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>
