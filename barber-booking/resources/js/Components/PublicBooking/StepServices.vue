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

function handleSelectMain(service) {
    // If already selected, let wizard handle it (it'll reselect + clear option/addon)
    props.wizard.selectMainService(service);
}
</script>

<template>
    <div>
        <h2 class="text-2xl font-black tracking-tight sm:text-4xl">
            Servicios
        </h2>

        <!-- Category tabs -->
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

        <!-- Main services with inline options -->
        <div class="mt-6 sm:mt-9">
            <div class="divide-y divide-gray-200">
                <div
                    v-for="service in filteredServices"
                    :key="service.id"
                    class=""
                >
                    <!-- Main service header — click to select/show options -->
                    <button
                        type="button"
                        class="group flex min-h-[60px] w-full items-center justify-between gap-4 py-4 text-left transition hover:bg-gray-50 sm:gap-6 sm:py-6"
                        @click="handleSelectMain(service)"
                    >
                        <div>
                            <h4
                                class="text-lg font-black tracking-tight group-hover:underline sm:text-xl"
                            >
                                {{ service.name }}
                            </h4>

                            <p
                                v-if="service.description"
                                class="mt-1 max-w-xl text-sm leading-relaxed text-gray-600 sm:mt-2 sm:text-base"
                            >
                                {{ service.description }}
                            </p>

                            <p
                                class="mt-2 text-sm text-gray-700 sm:mt-3 sm:text-base"
                            >
                                <span v-if="Number(service.price) > 0">
                                    USD {{ Number(service.price).toFixed(2) }}
                                </span>
                                <span v-else>El precio varía</span>
                                <span class="mx-2">·</span>
                                {{ service.duration_minutes }} min+
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
                                    wizard.selectedMainService.value?.id === service.id
                                "
                                class="h-2.5 w-2.5 rounded-full bg-white"
                            />
                        </div>
                    </button>

                    <!-- Options list — shown when this main is selected -->
                    <div
                        v-if="
                            wizard.selectedMainService.value?.id === service.id &&
                            wizard.serviceOptions.value.length > 0
                        "
                        class="ml-6 border-l-2 border-gray-200 pl-6 sm:ml-10 sm:pl-10"
                    >
                        <div class="divide-y divide-gray-100">
                            <button
                                v-for="option in wizard.serviceOptions.value"
                                :key="option.id"
                                type="button"
                                class="group flex w-full items-center justify-between gap-4 py-5 text-left transition hover:bg-gray-50 sm:gap-6 sm:py-6"
                                @click="wizard.selectServiceOption(option)"
                            >
                                <div>
                                    <h5
                                        class="text-base font-black tracking-tight group-hover:underline sm:text-lg"
                                    >
                                        {{ option.name }}
                                    </h5>

                                    <p
                                        v-if="option.description"
                                        class="mt-1 max-w-xl text-sm text-gray-600 sm:mt-2 sm:text-base"
                                    >
                                        {{ option.description }}
                                    </p>

                                    <p
                                        class="mt-2 text-sm text-gray-700 sm:mt-3 sm:text-base"
                                    >
                                        USD {{ Number(option.price).toFixed(2) }}
                                        <span class="mx-2">·</span>
                                        {{ option.duration_minutes }} min
                                    </p>

                                    <p
                                        v-if="
                                            wizard.selectedServiceOption.value?.id ===
                                            option.id
                                        "
                                        class="mt-2 text-sm font-black text-green-700"
                                    >
                                        ✓ Añadido
                                    </p>
                                </div>

                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-4 transition sm:h-11 sm:w-11"
                                    :class="
                                        wizard.selectedServiceOption.value?.id ===
                                        option.id
                                            ? 'border-gray-950 bg-gray-950'
                                            : 'border-gray-300 group-hover:border-gray-950'
                                    "
                                >
                                    <div
                                        v-if="
                                            wizard.selectedServiceOption.value
                                                ?.id === option.id
                                        "
                                        class="h-2 w-2 rounded-full bg-white sm:h-2.5 sm:w-2.5"
                                    />
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Addons section — shown when a service option is selected -->
        <div
            v-if="
                wizard.selectedServiceOption.value &&
                wizard.addons.value.length > 0
            "
            class="mt-10 sm:mt-12"
        >
            <h3 class="text-xl font-black tracking-tight sm:text-2xl">
                ¿Agregar más a tu cita?
            </h3>

            <div class="mt-4 divide-y divide-gray-200 sm:mt-5">
                <button
                    v-for="addon in wizard.addons.value"
                    :key="addon.id"
                    type="button"
                    class="group flex w-full items-center justify-between gap-4 rounded-2xl px-4 py-5 text-left transition hover:bg-[#f7f2ea] sm:gap-6 sm:py-6"
                    :class="
                        wizard.selectedAddon.value?.id === addon.id
                            ? 'bg-[#f5f0e8]'
                            : ''
                    "
                    @click="wizard.toggleAddon(addon)"
                >
                    <div>
                        <h4
                            class="text-base font-black group-hover:underline sm:text-lg"
                        >
                            {{ addon.name }}
                        </h4>

                        <p
                            v-if="addon.description"
                            class="mt-1 max-w-xl text-sm text-gray-600 sm:mt-2"
                        >
                            {{ addon.description }}
                        </p>

                        <p class="mt-2 text-sm text-gray-700 sm:mt-3">
                            USD {{ Number(addon.price).toFixed(2) }}
                            <span class="mx-2">·</span>
                            {{ addon.duration_minutes }} min
                        </p>

                        <p
                            v-if="
                                wizard.selectedAddon.value?.id === addon.id
                            "
                            class="mt-2 text-sm font-black text-green-700"
                        >
                            ✓ Añadido
                        </p>
                    </div>

                    <div
                        class="h-10 w-10 shrink-0 rounded-full border-4 transition sm:h-11 sm:w-11"
                        :class="
                            wizard.selectedAddon.value?.id === addon.id
                                ? 'border-gray-950 bg-gray-950'
                                : 'border-gray-300 group-hover:border-gray-950'
                        "
                    />
                </button>
            </div>
        </div>
    </div>
</template>
