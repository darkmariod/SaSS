<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import api from "@/services/api";
import ProTable from "@/components/ProTable.vue";
import ToastNotification from "@/components/ToastNotification.vue";

const cotizaciones = ref([]);
const clientes = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const error = ref("");
const success = ref("");
const toast = ref({ message: "", type: "success" });

const columns = [
    { key: "quote_number", label: "Código" },
    { key: "cliente", label: "Cliente", class: "hidden md:table-cell" },
    { key: "evento", label: "Evento" },
    { key: "subtotal", label: "Subtotal", align: "right", class: "hidden lg:table-cell" },
    { key: "total", label: "Total", align: "right" },
    { key: "status", label: "Estado" },
];

const form = reactive({
    cliente_id: "",
    quote_number: "",
    event_type: "",
    event_date: "",
    guests: 1,
    subtotal: 0,
    status: "pendiente",
    notes: "",
});

const isEditing = computed(() => Boolean(editingId.value));

const subtotalPreview = computed(() => roundMoney(form.subtotal));
const taxPreview = computed(() => roundMoney(subtotalPreview.value * 0.12));
const totalPreview = computed(() =>
    roundMoney(subtotalPreview.value + taxPreview.value),
);

const statusClasses = {
    pendiente: "bg-amber-50 text-amber-700",
    enviada: "bg-purple-50 text-purple-700",
    aprobada: "bg-emerald-50 text-emerald-700",
    rechazada: "bg-red-50 text-red-700",
};

function roundMoney(value) {
    return Math.round((Number(value || 0) + Number.EPSILON) * 100) / 100;
}

function formatMoney(value) {
    return `$${Number(value || 0).toFixed(2)}`;
}

const generateQuoteNumber = () => {
    const timestamp = Date.now().toString().slice(-6);
    form.quote_number = `PROP-${timestamp}`;
};

const resetForm = () => {
    editingId.value = null;
    form.cliente_id = "";
    form.quote_number = "";
    form.event_type = "";
    form.event_date = "";
    form.guests = 1;
    form.subtotal = 0;
    form.status = "pendiente";
    form.notes = "";
    generateQuoteNumber();
};

const fetchClientes = async () => {
    const { data } = await api.get("/clientes");
    clientes.value = data.data || data;
};

const fetchCotizaciones = async () => {
    const { data } = await api.get("/cotizaciones");
    cotizaciones.value = data.data || data;
};

const loadData = async () => {
    loading.value = true;
    await Promise.all([fetchClientes(), fetchCotizaciones()]);
    loading.value = false;
};

const submit = async () => {
    saving.value = true;
    error.value = "";
    success.value = "";

    try {
        const payload = {
            cliente_id: Number(form.cliente_id),
            quote_number: form.quote_number,
            event_type: form.event_type,
            event_date: form.event_date || null,
            guests: Number(form.guests || 1),
            subtotal: subtotalPreview.value,
            status: form.status,
            notes: form.notes,
        };

        if (isEditing.value) {
            await api.put(`/cotizaciones/${editingId.value}`, payload);
            success.value = "Propuesta actualizada correctamente.";
        } else {
            await api.post("/cotizaciones", payload);
            success.value = "Propuesta creada correctamente.";
        }

        resetForm();
        await fetchCotizaciones();
    } catch (exception) {
        error.value =
            exception.response?.data?.message ||
            "No se pudo guardar la propuesta.";
    } finally {
        saving.value = false;
    }
};

const editCotizacion = (cotizacion) => {
    editingId.value = cotizacion.id;
    form.cliente_id = cotizacion.cliente_id || "";
    form.quote_number = cotizacion.quote_number || "";
    form.event_type = cotizacion.event_type || "";
    form.event_date = cotizacion.event_date || "";
    form.guests = cotizacion.guests || 1;
    form.subtotal = cotizacion.subtotal || 0;
    form.status = cotizacion.status || "pendiente";
    form.notes = cotizacion.notes || "";
    window.scrollTo({ top: 0, behavior: "smooth" });
};

const deleteCotizacion = async (cotizacion) => {
    if (!confirm(`Eliminar propuesta ${cotizacion.quote_number}?`)) return;

    await api.delete(`/cotizaciones/${cotizacion.id}`);
    await fetchCotizaciones();
};

const exportCotizacion = async (cotizacion) => {
    try {
        const { data } = await api.get(
            `/cotizaciones/${cotizacion.id}/export`,
            {
                responseType: "blob",
            },
        );

        const url = window.URL.createObjectURL(new Blob([data]));
        const link = document.createElement("a");
        const clientName = cotizacion.cliente?.name || "cliente";

        link.href = url;
        link.setAttribute(
            "download",
            `${cotizacion.quote_number}-${clientName}.xlsx`,
        );
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (exception) {
        error.value =
            exception.response?.data?.message ||
            "No se pudo exportar la proforma.";
    }
};

const exportAllExcel = async () => {
    try {
        const { data } = await api.get(
            `/cotizaciones/${cotizaciones.value[0]?.id}/export`,
            { responseType: "blob" },
        );

        const url = window.URL.createObjectURL(new Blob([data]));
        const link = document.createElement("a");
        const timestamp = new Date().toISOString().slice(0, 10);

        link.href = url;
        link.setAttribute(
            "download",
            `Propuestas-BarFlowEC-${timestamp}.xlsx`,
        );
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        toast.value = {
            message: "Exportación completada correctamente.",
            type: "success",
        };
    } catch (exception) {
        toast.value = {
            message: "No se pudo exportar. Intenta de nuevo.",
            type: "error",
        };
    }
};

watch(
    () => form.subtotal,
    () => {
        form.subtotal = form.subtotal < 0 ? 0 : form.subtotal;
    },
);

onMounted(async () => {
    generateQuoteNumber();
    await loadData();
});
</script>

<template>
    <section class="grid gap-6 xl:grid-cols-[430px_1fr]">
        <form
            class="rounded-2xl bg-white p-6 shadow-sm"
            @submit.prevent="submit"
        >
            <div class="mb-5">
                <h3 class="text-lg font-bold text-slate-950">
                    {{ isEditing ? "Editar propuesta" : "Nueva propuesta" }}
                </h3>
                <p class="text-sm text-slate-500">
                    Crea una proforma clara para enviar al cliente.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Cliente</label
                    >
                    <select
                        v-model="form.cliente_id"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    >
                        <option value="">Seleccionar cliente</option>
                        <option
                            v-for="cliente in clientes"
                            :key="cliente.id"
                            :value="cliente.id"
                        >
                            {{ cliente.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Código de propuesta</label
                    >
                    <div class="mt-2 flex gap-2">
                        <input
                            v-model="form.quote_number"
                            required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                        />
                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 hover:bg-slate-50"
                            @click="generateQuoteNumber"
                        >
                            Generar
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Tipo de evento</label
                    >
                    <select
                        v-model="form.event_type"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    >
                        <option value="">Seleccionar tipo</option>
                        <option value="Boda">Boda</option>
                        <option value="Corporativo">Corporativo</option>
                        <option value="Cumpleaños">Cumpleaños</option>
                        <option value="Recepción">Recepción</option>
                        <option value="Social">Social</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-slate-700"
                            >Fecha del evento</label
                        >
                        <input
                            v-model="form.event_date"
                            type="date"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                        />
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700"
                            >Invitados</label
                        >
                        <input
                            v-model="form.guests"
                            type="number"
                            min="1"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                        />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Subtotal USD</label
                    >
                    <input
                        v-model="form.subtotal"
                        type="number"
                        min="0"
                        step="0.01"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p
                            class="text-xs font-semibold uppercase text-slate-500"
                        >
                            IVA 12%
                        </p>
                        <p class="mt-1 text-xl font-bold text-slate-950">
                            {{ formatMoney(taxPreview) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-purple-50 p-4">
                        <p
                            class="text-xs font-semibold uppercase text-purple-600"
                        >
                            Total proforma
                        </p>
                        <p class="mt-1 text-xl font-bold text-purple-700">
                            {{ formatMoney(totalPreview) }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Estado comercial</label
                    >
                    <select
                        v-model="form.status"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    >
                        <option value="pendiente">Pendiente</option>
                        <option value="enviada">Enviada</option>
                        <option value="aprobada">Aprobada</option>
                        <option value="rechazada">Rechazada</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Notas para la propuesta</label
                    >
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>
            </div>

            <p
                v-if="error"
                class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600"
            >
                {{ error }}
            </p>

            <p
                v-if="success"
                class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ success }}
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button
                    type="submit"
                    class="rounded-xl bg-purple-600 px-5 py-3 text-sm font-bold text-white hover:bg-purple-700"
                    :disabled="saving"
                >
                    {{
                        saving
                            ? "Guardando..."
                            : isEditing
                              ? "Actualizar propuesta"
                              : "Crear propuesta"
                    }}
                </button>

                <button
                    v-if="isEditing"
                    type="button"
                    class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
                    @click="resetForm"
                >
                    Cancelar
                </button>
            </div>
        </form>

        <article class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-bold text-slate-950">Propuestas</h3>
                    <span class="text-sm font-semibold text-slate-400"
                        >{{ cotizaciones.length }} registros</span
                    >
                </div>

                <!-- Botón Exportar Excel visible siempre -->
                <button
                    v-if="cotizaciones.length > 0"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700"
                    @click="exportAllExcel"
                >
                    <span>📊</span>
                    Exportar todo Excel
                </button>
            </div>

            <ProTable
                :columns="columns"
                :rows="cotizaciones"
                :loading="loading"
                empty-message="No hay propuestas registradas."
                empty-icon="📄"
            >
                <template #cell-cliente="{ row }">
                    <span class="text-slate-600">
                        {{ row.cliente?.name || "Sin cliente" }}
                    </span>
                </template>
                <template #cell-evento="{ row }">
                    <div>
                        <p>{{ row.event_type || "-" }}</p>
                        <p class="text-xs text-slate-400">
                            {{ row.event_date || "Sin fecha" }} ·
                            {{ row.guests }} inv.
                        </p>
                    </div>
                </template>
                <template #cell-subtotal="{ value }">
                    <span class="font-semibold text-slate-900">
                        {{ formatMoney(value) }}
                    </span>
                </template>
                <template #cell-total="{ value }">
                    <span class="font-bold text-slate-950">
                        {{ formatMoney(value) }}
                    </span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        class="rounded-full px-3 py-1 text-xs font-bold"
                        :class="
                            statusClasses[row.status] ||
                            'bg-slate-100 text-slate-700'
                        "
                    >
                        {{ row.status }}
                    </span>
                </template>
                <template #actions="{ row }">
                    <button
                        class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                        title="Exportar a Excel"
                        @click="exportCotizacion(row)"
                    >
                        📊 Excel
                    </button>
                    <button
                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        @click="editCotizacion(row)"
                    >
                        Editar
                    </button>
                    <button
                        class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100"
                        @click="deleteCotizacion(row)"
                    >
                        Eliminar
                    </button>
                </template>
            </ProTable>
        </article>
    </section>

    <ToastNotification
        :message="toast.message"
        :type="toast.type"
        @close="toast.message = ''"
    />
</template>
