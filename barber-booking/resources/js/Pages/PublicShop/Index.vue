<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";

const props = defineProps({
    shop: { type: Object, required: true },
    services: { type: Array, required: true },
    barbers: { type: Array, required: true },
    preselectedBarber: { type: Object, default: null },
});

// ─── Helpers ───────────────────────────────────────────────
const storageUrl = (path) => (path ? `/storage/${path}` : null);

const initials = computed(() => {
    if (!props.shop.name) return "B";
    return props.shop.name
        .split(" ")
        .map((w) => w[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
});

const coverSrc = computed(() => storageUrl(props.shop.cover_image));
const logoSrc = computed(() => storageUrl(props.shop.logo));

// ─── Dark Mode ─────────────────────────────────────────────
const darkMode = ref(false);

function toggleDarkMode() {
    darkMode.value = !darkMode.value;
    document.documentElement.classList.toggle("dark", darkMode.value);
    localStorage.setItem("darkMode", darkMode.value ? "true" : "false");
}

onMounted(() => {
    const stored = localStorage.getItem("darkMode");
    if (stored === "true") {
        darkMode.value = true;
        document.documentElement.classList.add("dark");
    }
});

// ─── Services (only bookable 'option' type) ────────────────
const bookableServices = computed(() =>
    props.services.filter((s) => s.service_type === "option"),
);

const categories = computed(() => {
    const cats = [
        ...new Set(bookableServices.value.map((s) => s.category || "General")),
    ];
    return ["Todos", ...cats];
});

const activeCategory = ref("Todos");
const searchQuery = ref("");
const selectedService = ref(null);

const filteredServices = computed(() => {
    let result = bookableServices.value;

    const q = searchQuery.value.trim().toLowerCase();
    if (q) {
        result = result.filter(
            (s) =>
                s.name.toLowerCase().includes(q) ||
                (s.description && s.description.toLowerCase().includes(q)),
        );
    }

    if (activeCategory.value !== "Todos") {
        result = result.filter(
            (s) => (s.category || "General") === activeCategory.value,
        );
    }

    return result;
});

const totalPrice = computed(() => {
    return selectedService.value ? Number(selectedService.value.price) : 0;
});

function selectService(service) {
    if (selectedService.value?.id === service.id) {
        selectedService.value = null;
    } else {
        selectedService.value = service;
    }
}

function scrollTo(id) {
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
}

function goToBooking() {
    if (!selectedService.value) return;
    router.get(`/barberia/${props.shop.slug}/reservar`, {
        service: selectedService.value.id,
    });
}

// ─── Hardcoded Reviews ─────────────────────────────────────
const reviews = [
    {
        name: "Andrés Martínez",
        text: "Roberto es un maestro. El corte quedó perfecto y el ambiente es increíble.",
        rating: 5,
    },
    {
        name: "Felipe Ruiz",
        text: "Mejor afeitada de navaja que he tenido. Diego es muy profesional.",
        rating: 5,
    },
    {
        name: "Lucas Vega",
        text: "Excelente corte moderno, me encantó. Precio justo y atención rápida.",
        rating: 4,
    },
];

const avgRating = 4.7;

// ─── Gallery Placeholders ──────────────────────────────────
const galleryPlaceholders = Array.from({ length: 6 }, (_, i) => i);

// ─── Social Links ──────────────────────────────────────────
const socialLinks = [
    { name: "Instagram", key: "instagram", href: props.shop.instagram || "#" },
    { name: "TikTok", key: "tiktok", href: props.shop.tiktok || "#" },
    { name: "Facebook", key: "facebook", href: props.shop.facebook || "#" },
    { name: "WhatsApp", key: "whatsapp", href: props.shop.whatsapp || "#" },
];

function socialHoverClass(key) {
    const map = {
        instagram:
            "hover:bg-gradient-to-br hover:from-purple-500 hover:to-pink-500 hover:text-white",
        tiktok: "hover:bg-black hover:text-white",
        facebook: "hover:bg-blue-600 hover:text-white",
        whatsapp: "hover:bg-green-500 hover:text-white",
    };
    return map[key] || "hover:bg-zinc-100 dark:hover:bg-zinc-700";
}
</script>

<template>
    <Head :title="shop.name" />

    <div class="min-h-screen bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 transition-colors">
        <!-- ═══════════════════════════════════════════════════
             STICKY GLASS HEADER
             ═══════════════════════════════════════════════════ -->
        <header
            class="header-glass fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-4 py-3 sm:px-6 lg:px-8"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"
                >
                    <img
                        v-if="logoSrc"
                        :src="logoSrc"
                        :alt="shop.name"
                        class="h-full w-full object-cover"
                    />
                    <span
                        v-else
                        class="text-xs font-bold text-zinc-500 dark:text-zinc-400"
                    >
                        {{ initials }}
                    </span>
                </div>
                <span class="text-sm font-bold tracking-tight">
                    {{ shop.name }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <!-- Dark Mode Toggle -->
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-zinc-500 transition hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    :title="darkMode ? 'Modo claro' : 'Modo oscuro'"
                    @click="toggleDarkMode"
                >
                    <!-- Sun icon (dark mode ON → switch to light) -->
                    <svg
                        v-if="darkMode"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"
                        />
                    </svg>
                    <!-- Moon icon (light mode → switch to dark) -->
                    <svg
                        v-else
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"
                        />
                    </svg>
                </button>

                <!-- Admin Login -->
                <a
                    :href="`/login`"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-zinc-500 transition hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    title="Admin"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"
                        />
                    </svg>
                </a>
            </div>
        </header>

        <!-- Header spacer -->
        <div class="h-[57px]" />

        <!-- ═══════════════════════════════════════════════════
             COVER BANNER
             ═══════════════════════════════════════════════════ -->
        <section class="relative mx-4 mt-4 sm:mx-6 lg:mx-8">
            <div class="relative h-48 overflow-hidden rounded-3xl sm:h-64 sm:rounded-[2.5rem] group">
                <!-- Cover Image -->
                <img
                    v-if="coverSrc"
                    :src="coverSrc"
                    :alt="`${shop.name} - Portada`"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div
                    v-else
                    class="absolute inset-0 bg-gradient-to-br from-zinc-800 via-zinc-700 to-zinc-600 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-700"
                />

                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/20" />

                <!-- Content centered over cover -->
                <div
                    class="relative z-10 flex h-full w-full flex-col items-center justify-center px-6 text-center"
                >
                    <!-- Logo -->
                    <div
                        class="mb-3 h-16 w-16 overflow-hidden rounded-full border-2 border-white shadow-lg transition-transform hover:scale-110 sm:h-20 sm:w-20"
                    >
                        <img
                            v-if="logoSrc"
                            :src="logoSrc"
                            :alt="shop.name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-white/20"
                        >
                            <span
                                class="text-xl font-extrabold text-white sm:text-2xl"
                            >
                                {{ initials }}
                            </span>
                        </div>
                    </div>

                    <!-- Business Name -->
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-white sm:text-5xl"
                    >
                        {{ shop.name }}
                    </h1>

                    <!-- Rating -->
                    <div class="mt-2 flex items-center gap-1.5">
                        <div class="flex items-center gap-0.5">
                            <svg
                                v-for="i in 5"
                                :key="i"
                                class="h-4 w-4 sm:h-5 sm:w-5"
                                :class="
                                    i <= Math.round(avgRating)
                                        ? 'text-yellow-400'
                                        : 'text-white/30'
                                "
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                        </div>
                        <span class="text-base font-bold text-white">{{ avgRating }}</span>
                        <span class="text-sm text-white/60">(3 opiniones)</span>
                    </div>

                    <!-- Description -->
                    <p
                        v-if="shop.description"
                        class="mt-3 max-w-xl text-sm leading-relaxed text-white/80 sm:text-base"
                    >
                        {{ shop.description }}
                    </p>

                    <!-- CTA Button -->
                    <button
                        type="button"
                        class="mt-5 inline-flex items-center gap-2 rounded-full bg-[#ff0000] px-8 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-red-700 active:scale-[0.97] sm:text-base"
                        @click="scrollTo('catalogo-servicios')"
                    >
                        Reservar Cita
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════
             INFO + SOCIAL CARD (glass background)
             ═══════════════════════════════════════════════════ -->
        <section
            class="mx-4 mt-6 rounded-2xl border border-zinc-100 bg-white/70 px-5 py-5 shadow-sm backdrop-blur-xl sm:mx-6 sm:px-8 sm:py-6 lg:mx-8 dark:border-zinc-800 dark:bg-zinc-900/70"
        >
            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-start sm:gap-8 lg:gap-12"
            >
                <!-- Ubicación -->
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-50 text-[#ff0000] dark:bg-red-950/50"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-zinc-400"
                        >
                            Ubicación
                        </p>
                        <p
                            class="mt-0.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 sm:text-base"
                        >
                            {{ shop.address || "Dirección no registrada" }}
                        </p>
                    </div>
                </div>

                <!-- Horario -->
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-50 text-[#ff0000] dark:bg-red-950/50"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-zinc-400"
                        >
                            Horario
                        </p>
                        <p
                            class="mt-0.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 sm:text-base"
                        >
                            Lun–Sáb 9:00–20:00
                        </p>
                        <p
                            class="text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            Dom 10:00–18:00
                        </p>
                    </div>
                </div>

                <!-- Spacer -->
                <div class="hidden sm:ml-auto sm:block" />

                <!-- Social Icons -->
                <div class="flex items-center gap-2">
                    <a
                        v-for="link in socialLinks"
                        :key="link.key"
                        :href="link.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition-all duration-200 dark:border-zinc-700 dark:text-zinc-400"
                        :class="socialHoverClass(link.key)"
                        :title="link.name"
                    >
                        <!-- Instagram -->
                        <svg
                            v-if="link.key === 'instagram'"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <rect
                                x="2"
                                y="2"
                                width="20"
                                height="20"
                                rx="5"
                                stroke="currentColor"
                                fill="none"
                            />
                            <circle
                                cx="12"
                                cy="12"
                                r="5"
                                stroke="currentColor"
                                fill="none"
                            />
                            <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" />
                        </svg>

                        <!-- TikTok -->
                        <svg
                            v-else-if="link.key === 'tiktok'"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19 8V6a3 3 0 00-3-3h-1a1 1 0 00-1 1v11a4 4 0 11-6-3.46V9.85A6.01 6.01 0 0016 15.54V8h3z"
                            />
                        </svg>

                        <!-- Facebook -->
                        <svg
                            v-else-if="link.key === 'facebook'"
                            class="h-5 w-5"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                            />
                        </svg>

                        <!-- WhatsApp -->
                        <svg
                            v-else-if="link.key === 'whatsapp'"
                            class="h-5 w-5"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"
                            />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════
             MAIN CONTENT
             ═══════════════════════════════════════════════════ -->
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- ─── GALLERY ─── -->
            <section class="scroll-mt-24">
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">
                        Nuestra Galería
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Conocé nuestro espacio y el trabajo que hacemos
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                    <div
                        v-for="(_, i) in galleryPlaceholders"
                        :key="i"
                        class="stagger-item aspect-square rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center"
                        :style="{ animationDelay: `${i * 80}ms` }"
                    >
                        <svg
                            class="h-8 w-8 text-zinc-300 dark:text-zinc-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"
                            />
                        </svg>
                    </div>
                </div>
            </section>

            <!-- ─── REVIEWS ─── -->
            <section class="mt-16 scroll-mt-24 sm:mt-20">
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">
                    Opiniones de clientes
                    </h2>
                </div>

                <!-- Rating Summary -->
                <div
                    class="mt-6 flex flex-col items-center gap-2 sm:flex-row sm:gap-4"
                >
                    <span class="text-5xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                        {{ avgRating }}
                    </span>
                    <div class="flex flex-col items-center sm:items-start">
                        <div class="flex items-center gap-0.5">
                            <svg
                                v-for="i in 5"
                                :key="i"
                                class="h-5 w-5"
                                :class="
                                    i <= Math.round(avgRating)
                                        ? 'text-yellow-400'
                                        : 'text-zinc-200 dark:text-zinc-700'
                                "
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                        </div>
                        <span class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        Basado en 3 reseñas
                        </span>
                    </div>
                </div>

                <!-- Review Cards (horizontal snap scroll) -->
                <div
                    class="mt-6 flex gap-4 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-hide"
                >
                    <div
                        v-for="(review, index) in reviews"
                        :key="index"
                        class="stagger-item min-w-[280px] snap-start rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm transition hover:shadow-md sm:min-w-[320px] dark:border-zinc-800 dark:bg-zinc-900"
                        :style="{ animationDelay: `${index * 100}ms` }"
                    >
                        <div class="flex items-center gap-1">
                            <svg
                                v-for="star in 5"
                                :key="star"
                                class="h-4 w-4"
                                :class="
                                    star <= review.rating
                                        ? 'text-yellow-400'
                                        : 'text-zinc-200 dark:text-zinc-700'
                                "
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                            "{{ review.text }}"
                        </p>
                        <p class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">
                            — {{ review.name }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ─── SERVICE CATALOG ─── -->
            <section id="catalogo-servicios" class="mt-16 scroll-mt-24 sm:mt-20">
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">
                        Reserva tu cita
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Elegí el servicio que querés reservar
                    </p>
                </div>

                <!-- Search Bar -->
                <div class="relative mt-6">
                    <svg
                        class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                        />
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Buscar servicios..."
                        class="w-full rounded-2xl border border-zinc-200 bg-white py-3 pl-12 pr-4 text-sm outline-none transition focus:border-[#ff0000] focus:ring-1 focus:ring-[#ff0000]/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-500"
                    />
                </div>

                <!-- Category Tabs -->
                <div
                    class="mt-5 flex gap-2 overflow-x-auto pb-2 scrollbar-hide"
                >
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        type="button"
                        class="shrink-0 rounded-full px-5 py-2 text-sm font-medium transition-all"
                        :class="
                            activeCategory === cat
                                ? 'bg-[#ff0000] text-white shadow-sm'
                                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700'
                        "
                        @click="activeCategory = cat"
                    >
                        {{ cat }}
                    </button>
                </div>

                <!-- Service Cards Grid -->
                <div
                    v-if="filteredServices.length > 0"
                    class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="(service, index) in filteredServices"
                        :key="service.id"
                        class="stagger-item cursor-pointer rounded-2xl border bg-white p-5 shadow-sm transition-all duration-200 dark:bg-zinc-900"
                        :class="
                            selectedService?.id === service.id
                                ? 'border-[#ff0000] ring-1 ring-[#ff0000]/20 shadow-md'
                                : 'border-zinc-100 dark:border-zinc-800 hover:shadow-md hover:-translate-y-0.5'
                        "
                        :style="{ animationDelay: `${index * 60}ms` }"
                        @click="selectService(service)"
                        role="radio"
                        :aria-checked="selectedService?.id === service.id"
                        tabindex="0"
                        @keydown.enter="selectService(service)"
                        @keydown.space.prevent="selectService(service)"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3
                                    class="text-base font-bold tracking-tight text-zinc-900 dark:text-zinc-100"
                                >
                                    {{ service.name }}
                                </h3>
                                <p
                                    v-if="service.description"
                                    class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400"
                                >
                                    {{ service.description }}
                                </p>
                            </div>

                            <!-- Selection check indicator -->
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition"
                                :class="
                                    selectedService?.id === service.id
                                        ? 'border-[#ff0000] bg-[#ff0000] text-white'
                                        : 'border-zinc-300 dark:border-zinc-600'
                                "
                            >
                                <svg
                                    v-if="selectedService?.id === service.id"
                                    class="h-3.5 w-3.5"
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
                        </div>

                        <div
                            class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1"
                        >
                            <span
                                class="text-lg font-bold text-[#ff0000]"
                            >
                                ${{ Number(service.price).toFixed(2) }}
                            </span>
                            <span class="text-zinc-300 dark:text-zinc-700">·</span>
                            <span class="flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400">
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                {{ service.duration_minutes }} min
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="mt-10 flex flex-col items-center justify-center py-16 text-center"
                >
                    <svg
                        class="mb-4 h-12 w-12 text-zinc-300 dark:text-zinc-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                        />
                    </svg>
                    <p class="text-lg font-semibold text-zinc-500 dark:text-zinc-400">
                    No se encontraron servicios
                    </p>
                    <p class="mt-1 text-sm text-zinc-400 dark:text-zinc-500">
                    Intentá con otro término de búsqueda o categoría
                    </p>
                </div>
            </section>
        </div>

        <!-- ═══════════════════════════════════════════════════
             FOOTER
             ═══════════════════════════════════════════════════ -->
        <footer class="border-t border-zinc-100 bg-white dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-6xl px-4 py-8 text-center sm:px-6 lg:px-8">
                <p class="text-sm text-zinc-400 dark:text-zinc-500">
                    &copy; {{ new Date().getFullYear() }} {{ shop.name }}.
                    Todos los derechos reservados.
                </p>
            </div>
        </footer>

        <!-- ═══════════════════════════════════════════════════
             ACTION BAR (fixed mobile / sticky desktop)
             ═══════════════════════════════════════════════════ -->
        <div
            class="fixed bottom-0 left-0 right-0 z-50 border-t border-zinc-200 bg-white/80 px-4 py-3 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/80"
        >
            <div
                class="mx-auto flex max-w-6xl items-center justify-between gap-4"
            >
                <div class="flex items-baseline gap-1.5">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Total:</span>
                    <span
                        class="text-xl font-bold"
                        :class="
                            selectedService
                                ? 'text-zinc-900 dark:text-zinc-100'
                                : 'text-zinc-400 dark:text-zinc-600'
                        "
                    >
                        USD {{ totalPrice.toFixed(2) }}
                    </span>
                </div>

                <button
                    type="button"
                    :disabled="!selectedService"
                    class="rounded-full px-10 py-3 text-sm font-bold text-white transition-all duration-200 disabled:cursor-not-allowed"
                    :class="
                        selectedService
                            ? 'bg-[#ff0000] hover:bg-red-700 shadow-lg hover:shadow-xl active:scale-[0.97]'
                            : 'bg-zinc-300 dark:bg-zinc-700'
                    "
                    @click="goToBooking"
                >
                Continuar
                </button>
            </div>
        </div>

        <!-- Spacer for the fixed action bar -->
        <div class="h-20" />
    </div>
</template>

<style>
/* ─── Glass Header ──────────────────────────────────────── */
.header-glass {
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.dark .header-glass {
    background: rgba(9, 9, 11, 0.75);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

/* ─── Animations ────────────────────────────────────────── */
@keyframes fade-in {
    from { opacity: 0; }
    to   { opacity: 1; }
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.5s ease forwards;
}

.animate-slide-up {
    animation: slide-up 0.5s ease forwards;
}

.stagger-item {
    opacity: 0;
    animation: slide-up 0.4s ease forwards;
}

/* ─── Hide scrollbar (reviews / categories) ─────────────── */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
