<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    shop: { type: Object, required: true },
    services: { type: Array, required: true },
    barbers: { type: Array, required: true },
    preselectedBarber: { type: Object, default: null },
});

const storageUrl = (path) => (path ? `/storage/${path}` : null);

const initials = () => {
    if (!props.shop.name) return "B";
    return props.shop.name
        .split(" ")
        .map((w) => w[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
};

const socialLinks = [
    { name: "Instagram", key: "instagram", href: props.shop.instagram || "#", color: "hover:bg-gradient-to-br hover:from-purple-500 hover:to-pink-500 hover:text-white" },
    { name: "TikTok", key: "tiktok", href: props.shop.tiktok || "#", color: "hover:bg-black hover:text-white" },
    { name: "Facebook", key: "facebook", href: props.shop.facebook || "#", color: "hover:bg-blue-600 hover:text-white" },
    { name: "WhatsApp", key: "whatsapp", href: props.shop.whatsapp || "#", color: "hover:bg-green-500 hover:text-white" },
];
</script>

<template>
    <Head :title="shop.name" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-b from-zinc-50 to-zinc-100 px-6 dark:from-zinc-950 dark:to-zinc-900">
        <!-- Avatar / Logo -->
        <div class="mb-6 h-28 w-28 overflow-hidden rounded-full border-4 border-white shadow-xl sm:h-32 sm:w-32">
            <img
                v-if="storageUrl(shop.logo)"
                :src="storageUrl(shop.logo)"
                :alt="shop.name"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-zinc-800 to-zinc-700"
            >
                <span class="text-3xl font-extrabold text-white sm:text-4xl">
                    {{ initials() }}
                </span>
            </div>
        </div>

        <!-- Business Name -->
        <h1 class="text-center text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-3xl">
            {{ shop.name }}
        </h1>

        <!-- Address -->
        <p class="mt-2 text-center text-sm text-zinc-500 dark:text-zinc-400">
            {{ shop.address || "Riobamba, Ecuador" }}
        </p>

        <!-- Social Icons -->
        <div class="mt-8 flex items-center gap-3">
            <a
                v-for="link in socialLinks"
                :key="link.key"
                :href="link.href"
                target="_blank"
                rel="noopener noreferrer"
                class="flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition-all duration-200 dark:border-zinc-700 dark:text-zinc-400"
                :class="link.color"
                :title="link.name"
            >
                <!-- Instagram -->
                <svg v-if="link.key === 'instagram'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" fill="none" />
                    <circle cx="12" cy="12" r="5" stroke="currentColor" fill="none" />
                    <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" />
                </svg>
                <!-- TikTok -->
                <svg v-else-if="link.key === 'tiktok'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 8V6a3 3 0 00-3-3h-1a1 1 0 00-1 1v11a4 4 0 11-6-3.46V9.85A6.01 6.01 0 0016 15.54V8h3z" />
                </svg>
                <!-- Facebook -->
                <svg v-else-if="link.key === 'facebook'" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                </svg>
                <!-- WhatsApp -->
                <svg v-else-if="link.key === 'whatsapp'" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                </svg>
            </a>
        </div>

        <!-- Reservar Cita Button -->
        <Link
            :href="`/barberia/${shop.slug}/reservar`"
            class="mt-10 inline-flex items-center gap-2 rounded-full bg-zinc-900 px-10 py-3.5 text-base font-bold text-white shadow-lg transition hover:bg-zinc-800 active:scale-[0.97] dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
            Reservar Cita
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </Link>
    </div>
</template>
