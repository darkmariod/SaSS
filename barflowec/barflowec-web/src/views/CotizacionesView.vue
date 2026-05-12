<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import api from "@/services/api";

const cotizaciones = ref([]);
const clientes = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const error = ref("");

const form = reactive({
    cliente_id: "",
    quote_number: "",
    event_type: "",
    event_date: "",
    guests: 1,
    subtotal: 0,
    tax: 0,
    total: 0,
    status: "pendiente",
    notes: "",
});

const isEditing = computed(() => Boolean(editingId.value));

const statusClasses = {
    pendiente: "bg-amber-50 text-amber-700",
    enviada: "bg-purple-50 text-purple-700",
    aprobada: "bg-emerald-50 text-emerald-700",
    rechazada: "bg-red-50 text-red-700",
};

const generateQuoteNumber = () => {
    const timestamp = Date.now().toString().slice(-6);
    form.quote_number = `COT-${timestamp}`;
};

const calculateTotals = () => {
    const subtotal = Number(form.subtotal || 0);
    const tax = Number((subtotal * 0.12).toFixed(2));

    form.tax = tax;
    form.total = Number((subtotal + tax).toFixed(2));
};

watch(() => form.subtotal, calculateTotals);

const resetForm = () => {
    editingId.value = null;
    form.cliente_id = "";
    form.quote_number = "";
    form.event_type = "";
    form.event_date = "";
    form.guests = 1;
    form.subtotal = 0;
    form.tax = 0;
    form.total = 0;
    form.status = "pendiente";
    form.notes = "";
    generateQuoteNumber();
};

const fetchClientes = async () => {
    const { data } = await api.get("/clientes");
    clientes.value = data.data || data;
};

const fetchCotizaciones = async () => {
    loading.value = true;
    const { data } = await api.get("/cotizaciones");
    cotizaciones.value = data.data || data;
    loading.value = false;
};

const loadData = async () => {
    loading.value = true;
    await Promise.all([fetchClientes(), fetchCotizaciones()]);
    loading.value = false;
};

const submit = async () => {
    saving.value = true;
    error.value = "";

    try {
        calculateTotals();

        const payload = {
            ...form,
            cliente_id: Number(form.cliente_id),
            guests: Number(form.guests),
            subtotal: Number(form.subtotal),
            tax: Number(form.tax),
            total: Number(form.total),
        };

        if (isEditing.value) {
            await api.put(`/cotizaciones/${editingId.value}`, payload);
        } else {
            await api.post("/cotizaciones", payload);
        }

        resetForm();
        await fetchCotizaciones();
    } catch (exception) {
        error.value =
            exception.response?.data?.message ||
            "No se pudo guardar la cotización.";
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
    form.tax = cotizacion.tax || 0;
    form.total = cotizacion.total || 0;
    form.status = cotizacion.status || "pendiente";
    form.notes = cotizacion.notes || "";
};

const deleteCotizacion = async (cotizacion) => {
    if (!confirm(`Eliminar cotización ${cotizacion.quote_number}?`)) return;

    await api.delete(`/cotizaciones/${cotizacion.id}`);
    await fetchCotizaciones();
};

const formatMoney = (value) => `$${Number(value || 0).toFixed(2)}`;

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
                    {{ isEditing ? "Editar cotización" : "Nueva cotización" }}
                </h3>
                <p class="text-sm text-slate-500">
                    Crea propuestas comerciales para eventos.
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
                        >Número</label
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
                        <option value="Privado">Privado</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-700"
                            >Fecha</label
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
                        >Subtotal</label
                    >
                    <input
                        v-model="form.subtotal"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p
                            class="text-xs font-semibold uppercase text-slate-500"
                        >
                            IVA 12%
                        </p>
                        <p class="mt-1 text-xl font-bold text-slate-950">
                            {{ formatMoney(form.tax) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-purple-50 p-4">
                        <p
                            class="text-xs font-semibold uppercase text-purple-600"
                        >
                            Total
                        </p>
                        <p class="mt-1 text-xl font-bold text-purple-700">
                            {{ formatMoney(form.total) }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Estado</label
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
                        >Notas</label
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

            <div class="mt-6 flex gap-3">
                <button
                    type="submit"
                    class="rounded-xl bg-purple-600 px-5 py-3 text-sm font-bold text-white hover:bg-purple-700"
                    :disabled="saving"
                >
                    {{
                        saving
                            ? "Guardando..."
                            : isEditing
                              ? "Actualizar"
                              : "Crear"
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
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-950">Cotizaciones</h3>
                <span class="text-sm font-semibold text-slate-400"
                    >{{ cotizaciones.length }} registros</span
                >
            </div>

            <div v-if="loading" class="py-10 text-center text-slate-500">
                Cargando cotizaciones...
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-500">
                            <th class="py-3 font-semibold">Número</th>
                            <th class="py-3 font-semibold">Cliente</th>
                            <th class="py-3 font-semibold">Evento</th>
                            <th class="py-3 font-semibold">Total</th>
                            <th class="py-3 font-semibold">Estado</th>
                            <th class="py-3 text-right font-semibold">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="cotizacion in cotizaciones"
                            :key="cotizacion.id"
                            class="border-b border-slate-50"
                        >
                            <td class="py-4 font-bold text-slate-900">
                                {{ cotizacion.quote_number }}
                            </td>
                            <td class="py-4 text-slate-600">
                                {{ cotizacion.cliente?.name || "Sin cliente" }}
                            </td>
                            <td class="py-4 text-slate-600">
                                <p>{{ cotizacion.event_type || "-" }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ cotizacion.event_date || "Sin fecha" }} ·
                                    {{ cotizacion.guests }} invitados
                                </p>
                            </td>
                            <td class="py-4 font-semibold text-slate-900">
                                {{ formatMoney(cotizacion.total) }}
                            </td>
                            <td class="py-4">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold"
                                    :class="
                                        statusClasses[cotizacion.status] ||
                                        'bg-slate-100 text-slate-700'
                                    "
                                >
                                    {{ cotizacion.status }}
                                </span>
                            </td>
                            <td class="py-4 text-right">
                                <button
                                    class="mr-2 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                                    @click="editCotizacion(cotizacion)"
                                >
                                    Editar
                                </button>
                                <button
                                    class="rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-600 hover:bg-red-100"
                                    @click="deleteCotizacion(cotizacion)"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</template>
