<script setup>
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    shop: { type: Object, required: true },
    services: { type: Array, required: true },
    barbers: { type: Array, required: true },
    preselectedBarber: { type: Object, default: null },
});

const storageUrl = (path) => (path ? `/storage/${path}` : null);

/**
 * La carta: sólo lo que el cliente puede reservar y tiene precio. Los servicios
 * "main" son contenedores (precio 0) y no van en la lista de precios.
 */
const carta = computed(() =>
    props.services
        .filter((s) => s.service_type === "option" && Number(s.price) > 0)
        .sort((a, b) => Number(a.sort_order) - Number(b.sort_order)),
);

const equipo = computed(() => props.barbers.filter((b) => b.is_active !== false));

const precio = (valor) => `$${Number(valor).toFixed(2)}`;

const iniciales = (nombre) =>
    (nombre || "?")
        .split(" ")
        .map((p) => p[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);

const redes = computed(() =>
    [
        { key: "instagram", nombre: "Instagram", href: props.shop.instagram },
        { key: "tiktok", nombre: "TikTok", href: props.shop.tiktok },
        { key: "facebook", nombre: "Facebook", href: props.shop.facebook },
        { key: "whatsapp", nombre: "WhatsApp", href: props.shop.whatsapp },
    ].filter((r) => r.href),
);

const telefonoLimpio = computed(() =>
    (props.shop.phone || "").replace(/[^0-9+]/g, ""),
);

const reservarUrl = computed(() => `/barberia/${props.shop.slug}/reservar`);
</script>

<template>
    <Head :title="shop.name" />

    <div class="min-h-screen bg-[#F7F4EF] pb-28 text-[#17130E] dark:bg-[#12100D] dark:text-[#F2EBE0]">
        <!-- Cabecera: el letrero de la barbería -->
        <header class="relative overflow-hidden bg-[#17130E] text-[#F2EBE0]">
            <img
                v-if="storageUrl(shop.cover_image)"
                :src="storageUrl(shop.cover_image)"
                alt=""
                class="absolute inset-0 h-full w-full object-cover opacity-25"
            />

            <div class="relative mx-auto max-w-2xl px-6 pb-9 pt-12">
                <p class="text-[0.68rem] font-bold uppercase tracking-[0.32em] text-[#C9A24C]">
                    Reserva en línea
                </p>

                <div class="mt-4 flex items-start gap-4">
                    <img
                        v-if="storageUrl(shop.logo)"
                        :src="storageUrl(shop.logo)"
                        :alt="shop.name"
                        class="h-16 w-16 shrink-0 rounded-lg object-cover ring-1 ring-[#C9A24C]/40"
                    />
                    <div>
                        <h1 class="text-[2.1rem] font-black uppercase leading-[0.95] tracking-tight sm:text-5xl">
                            {{ shop.name }}
                        </h1>
                        <p v-if="shop.address || shop.city" class="mt-2 text-sm text-[#B9AE9D]">
                            {{ [shop.address, shop.city].filter(Boolean).join(" · ") }}
                        </p>
                    </div>
                </div>

                <p v-if="shop.description" class="mt-5 max-w-prose text-[0.95rem] leading-relaxed text-[#CFC5B5]">
                    {{ shop.description }}
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <a
                        v-if="telefonoLimpio"
                        :href="`tel:${telefonoLimpio}`"
                        class="rounded-md border border-[#3A322A] px-3 py-2 text-sm font-semibold text-[#F2EBE0] transition hover:border-[#C9A24C] hover:text-[#C9A24C]"
                    >
                        {{ shop.phone }}
                    </a>
                    <a
                        v-for="r in redes"
                        :key="r.key"
                        :href="r.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-md border border-[#3A322A] px-3 py-2 text-sm font-semibold text-[#B9AE9D] transition hover:border-[#C9A24C] hover:text-[#C9A24C]"
                    >
                        {{ r.nombre }}
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-2xl px-6">
            <!-- La carta de precios -->
            <section v-if="carta.length" class="pt-11">
                <div class="flex items-baseline justify-between border-b-2 border-[#17130E] pb-2 dark:border-[#C9A24C]">
                    <h2 class="text-xl font-black uppercase tracking-tight">Servicios</h2>
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#8A7F70]">
                        Precio · Duración
                    </span>
                </div>

                <ul class="divide-y divide-[#E2D9CA] dark:divide-[#2A241C]">
                    <li
                        v-for="servicio in carta"
                        :key="servicio.id"
                        class="flex items-baseline gap-4 py-4"
                    >
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold leading-snug">{{ servicio.name }}</h3>
                            <p
                                v-if="servicio.description"
                                class="mt-0.5 text-sm leading-snug text-[#7A7063] dark:text-[#9C9184]"
                            >
                                {{ servicio.description }}
                            </p>
                        </div>

                        <!-- Los puntos guía son de una carta impresa, no adorno:
                             ayudan a seguir la línea del nombre hasta el precio. -->
                        <span
                            aria-hidden="true"
                            class="hidden flex-1 translate-y-[-0.3rem] border-b border-dotted border-[#C6BAA6] sm:block dark:border-[#3A322A]"
                        />

                        <div class="shrink-0 text-right tabular-nums">
                            <div class="font-black">{{ precio(servicio.price) }}</div>
                            <div class="text-xs text-[#8A7F70]">{{ servicio.duration_minutes }} min</div>
                        </div>
                    </li>
                </ul>
            </section>

            <!-- El equipo -->
            <section v-if="equipo.length" class="pt-11">
                <div class="border-b-2 border-[#17130E] pb-2 dark:border-[#C9A24C]">
                    <h2 class="text-xl font-black uppercase tracking-tight">
                        {{ equipo.length === 1 ? "Tu barbero" : "El equipo" }}
                    </h2>
                </div>

                <ul class="grid grid-cols-2 gap-3 pt-5 sm:grid-cols-3">
                    <li
                        v-for="barbero in equipo"
                        :key="barbero.id"
                        class="rounded-lg border border-[#E2D9CA] bg-white p-4 dark:border-[#2A241C] dark:bg-[#1A1712]"
                    >
                        <img
                            v-if="storageUrl(barbero.photo)"
                            :src="storageUrl(barbero.photo)"
                            :alt="barbero.display_name"
                            class="mb-3 h-14 w-14 rounded-md object-cover"
                        />
                        <div
                            v-else
                            class="mb-3 flex h-14 w-14 items-center justify-center rounded-md bg-[#17130E] text-sm font-black text-[#C9A24C]"
                        >
                            {{ iniciales(barbero.display_name) }}
                        </div>

                        <p class="font-bold leading-snug">{{ barbero.display_name }}</p>

                        <Link
                            :href="`${reservarUrl}?b=${barbero.id}`"
                            class="mt-2 inline-block text-sm font-semibold text-[#8A6A1F] underline underline-offset-4 hover:text-[#17130E] dark:text-[#C9A24C] dark:hover:text-[#F2EBE0]"
                        >
                            Reservar con {{ barbero.display_name.split(" ")[0] }}
                        </Link>
                    </li>
                </ul>
            </section>

            <p class="pt-11 text-sm leading-relaxed text-[#7A7063] dark:text-[#9C9184]">
                Elegís el servicio, el barbero y la hora. La cita queda apartada al instante:
                nadie más puede tomar ese horario.
            </p>
        </main>

        <!-- Reservar: siempre a mano, sin tener que volver arriba -->
        <div
            class="fixed inset-x-0 bottom-0 border-t border-[#E2D9CA] bg-[#F7F4EF]/95 backdrop-blur dark:border-[#2A241C] dark:bg-[#12100D]/95"
        >
            <div class="mx-auto max-w-2xl px-6 py-4">
                <Link
                    :href="reservarUrl"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#17130E] px-6 py-4 text-base font-black uppercase tracking-wide text-[#F2EBE0] transition active:scale-[0.99] dark:bg-[#C9A24C] dark:text-[#17130E]"
                >
                    Reservar cita
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </Link>
            </div>
        </div>
    </div>
</template>
