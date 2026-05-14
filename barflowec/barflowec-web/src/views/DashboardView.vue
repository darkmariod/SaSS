<script setup>
import { computed, onMounted, ref } from "vue";
import api from "@/services/api";
import ProTable from "@/components/ProTable.vue";
import ToastNotification from "@/components/ToastNotification.vue";

const dashboard = ref(null);
const loading = ref(true);
const error = ref("");
const toast = ref({ message: "", type: "success" });

const colorClasses = {
    purple: "bg-purple-600 text-white",
    green: "bg-emerald-500 text-white",
    yellow: "bg-amber-400 text-slate-950",
    red: "bg-rose-500 text-white",
};

const metrics = computed(() => dashboard.value?.metrics || []);
const quotes = computed(() => dashboard.value?.recent_quotes || []);
const events = computed(() => dashboard.value?.upcoming_events || []);
const income = computed(
    () => dashboard.value?.income || { paid: 0, pending: 0 },
);

const formatMoney = (value) => `$${Number(value || 0).toFixed(2)}`;

const statusClass = (status) => {
    return (
        {
            borrador: "bg-slate-100 text-slate-700",
            aprobada: "bg-emerald-50 text-emerald-700",
            pendiente: "bg-amber-50 text-amber-700",
            enviada: "bg-purple-50 text-purple-700",
            rechazada: "bg-red-50 text-red-700",
        }[status] || "bg-slate-100 text-slate-700"
    );
};

const quoteColumns = [
    { key: "code", label: "Código" },
    { key: "client", label: "Cliente" },
    { key: "event_type", label: "Evento" },
    { key: "amount", label: "Valor", align: "right" },
    { key: "status", label: "Estado" },
];

onMounted(async () => {
    try {
        const { data } = await api.get("/dashboard");
        dashboard.value = data;
    } catch (exception) {
        console.error(exception);
        error.value =
            "No se pudo cargar el panel comercial. Inicia sesión nuevamente.";
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <section
        v-if="loading"
        class="rounded-2xl bg-white p-8 text-slate-500 shadow-sm"
    >
        Cargando panel comercial...
    </section>

    <section v-else-if="error" class="rounded-2xl bg-white p-8 shadow-sm">
        <h3 class="text-lg font-bold text-slate-950">Sesión expirada</h3>
        <p class="mt-2 text-sm text-slate-500">{{ error }}</p>
        <a
            href="/login"
            class="mt-5 inline-flex rounded-xl bg-purple-600 px-5 py-3 text-sm font-bold text-white hover:bg-purple-700"
        >
            Ir al login
        </a>
    </section>

    <section v-else class="space-y-6">
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="metric in metrics"
                :key="metric.label"
                class="rounded-2xl p-5 shadow-sm"
                :class="colorClasses[metric.color]"
            >
                <p class="text-sm opacity-80">{{ metric.label }}</p>

                <div class="mt-4 flex items-end justify-between">
                    <strong class="text-3xl font-bold">{{
                        metric.value
                    }}</strong>
                    <span
                        class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold"
                    >
                        {{ metric.change }}
                    </span>
                </div>
            </article>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <article class="rounded-2xl bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase text-slate-400">
                    Ingresos cobrados
                </p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">
                    {{ formatMoney(income.paid) }}
                </p>
            </article>

            <article class="rounded-2xl bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase text-slate-400">
                    Saldo pendiente
                </p>
                <p class="mt-2 text-3xl font-bold text-rose-600">
                    {{ formatMoney(income.pending) }}
                </p>
            </article>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
            <article class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-950">
                        Propuestas recientes
                    </h3>
                    <span class="text-sm text-slate-400"
                        >Pipeline comercial</span
                    >
                </div>

                <ProTable
                    :columns="quoteColumns"
                    :rows="quotes"
                    :loading="false"
                    empty-message="No hay propuestas registradas."
                    empty-icon="📄"
                >
                    <template #cell-code="{ row }">
                        <span class="font-semibold text-slate-900">
                            {{ row.code || `#${row.id}` }}
                        </span>
                    </template>
                    <template #cell-amount="{ value }">
                        <span class="font-semibold text-slate-900">
                            {{ formatMoney(value) }}
                        </span>
                    </template>
                    <template #cell-status="{ value }">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="statusClass(value)"
                        >
                            {{ value }}
                        </span>
                    </template>
                </ProTable>
            </article>

            <article class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-5 text-lg font-bold text-slate-950">
                    Próximos eventos
                </h3>

                <div
                    v-if="events.length === 0"
                    class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-slate-500"
                >
                    No hay eventos próximos.
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="event in events"
                        :key="`${event.name}-${event.date}`"
                        class="rounded-xl border border-slate-100 p-4"
                    >
                        <p class="font-semibold text-slate-950">
                            {{ event.name }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ event.date }} - {{ event.location }}
                        </p>
                        <p
                            class="mt-3 text-xs font-semibold uppercase text-purple-600"
                        >
                            {{ event.responsible || "Por asignar" }}
                        </p>
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>
