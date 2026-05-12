<script setup>
import { computed } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const modules = [
    { label: "Resumen", route: "dashboard" },
    { label: "Clientes", route: "clientes" },
    { label: "Servicios", route: "recetas" },
    { label: "Recursos / Inventario", route: "ingredientes" },
    { label: "Paquetes", route: "paquetes" },
    { label: "Propuestas", route: "cotizaciones" },
    { label: "Agenda", route: "eventos" },
    { label: "Cobros", route: "pagos" },
];

const roleLabels = {
    super_admin: "Administrador principal",
    admin: "Administrador",
    bartender: "Operador",
    asistente: "Asistente comercial",
};

const pageTitle = computed(() => route.meta.title || "Panel comercial");
const pageSubtitle = computed(
    () => route.meta.subtitle || "Gestión comercial y operativa de eventos",
);
const currentRole = computed(
    () => roleLabels[auth.user?.role] || auth.user?.role || "Usuario",
);

const logout = async () => {
    await auth.logout();
    router.push({ name: "login" });
};
</script>

<template>
    <div class="min-h-screen bg-slate-100 lg:flex">
        <aside class="bg-slate-950 text-white lg:fixed lg:inset-y-0 lg:w-72">
            <div class="flex h-24 items-center px-6">
                <div>
                    <h1 class="text-2xl font-bold">BarFlowEC</h1>
                    <p class="text-xs font-medium text-slate-400">
                        Gestión de eventos
                    </p>
                </div>
            </div>

            <nav class="grid gap-1 px-4 pb-6">
                <RouterLink
                    v-for="item in modules"
                    :key="item.label"
                    :to="{ name: item.route }"
                    class="rounded-xl px-4 py-3 text-left text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
                    :class="{
                        'bg-purple-600 text-white': route.name === item.route,
                    }"
                >
                    {{ item.label }}
                </RouterLink>
            </nav>
        </aside>

        <div class="min-w-0 flex-1 lg:pl-72">
            <header
                class="sticky top-0 z-10 flex h-24 items-center justify-between border-b border-slate-200 bg-white/90 px-6 backdrop-blur"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-950">
                        {{ pageTitle }}
                    </h2>
                    <p class="text-sm text-slate-500">{{ pageSubtitle }}</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden text-right sm:block">
                        <p
                            class="text-xs font-semibold uppercase text-slate-400"
                        >
                            Sesión activa
                        </p>
                        <p class="text-sm font-bold text-slate-900">
                            {{ auth.user?.name || "Super Admin" }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ currentRole }}
                        </p>
                    </div>

                    <button
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        @click="logout"
                    >
                        Salir
                    </button>
                </div>
            </header>

            <main class="p-6">
                <RouterView />
            </main>
        </div>
    </div>
</template>
