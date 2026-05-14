<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import api from "@/services/api";
import ProTable from "@/components/ProTable.vue";
import ToastNotification from "@/components/ToastNotification.vue";

const ingredientes = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const error = ref("");
const toast = ref({ message: "", type: "success" });

const form = reactive({
    name: "",
    unit: "unidad",
    stock: 0,
    min_stock: 0,
    cost: 0,
    status: "activo",
});

const isEditing = computed(() => Boolean(editingId.value));

const resetForm = () => {
    editingId.value = null;
    form.name = "";
    form.unit = "unidad";
    form.stock = 0;
    form.min_stock = 0;
    form.cost = 0;
    form.status = "activo";
};

const fetchIngredientes = async () => {
    loading.value = true;
    const { data } = await api.get("/ingredientes");
    ingredientes.value = data.data || data;
    loading.value = false;
};

const submit = async () => {
    saving.value = true;
    error.value = "";

    try {
        const payload = {
            ...form,
            stock: Number(form.stock),
            min_stock: Number(form.min_stock),
            cost: Number(form.cost),
        };

        if (isEditing.value) {
            await api.put(`/ingredientes/${editingId.value}`, payload);
        } else {
            await api.post("/ingredientes", payload);
        }

        resetForm();
        await fetchIngredientes();
        toast.value = {
            message: isEditing.value
                ? "Ingrediente actualizado correctamente."
                : "Ingrediente creado correctamente.",
            type: "success",
        };
    } catch (exception) {
        error.value =
            exception.response?.data?.message ||
            "No se pudo guardar el ingrediente.";
    } finally {
        saving.value = false;
    }
};

const editIngrediente = (ingrediente) => {
    editingId.value = ingrediente.id;
    form.name = ingrediente.name || "";
    form.unit = ingrediente.unit || "unidad";
    form.stock = ingrediente.stock || 0;
    form.min_stock = ingrediente.min_stock || 0;
    form.cost = ingrediente.cost || 0;
    form.status = ingrediente.status || "activo";
};

const deleteIngrediente = async (ingrediente) => {
    if (!confirm(`Eliminar ingrediente ${ingrediente.name}?`)) return;

    await api.delete(`/ingredientes/${ingrediente.id}`);
    await fetchIngredientes();
    toast.value = {
        message: "Ingrediente eliminado correctamente.",
        type: "success",
    };
};

const formatMoney = (value) => `$${Number(value || 0).toFixed(2)}`

const columns = [
    { key: "name", label: "Recurso" },
    { key: "unit", label: "Unidad", class: "hidden md:table-cell" },
    { key: "stock", label: "Stock", align: "right" },
    { key: "cost", label: "Costo", align: "right", class: "hidden lg:table-cell" },
    { key: "status", label: "Estado" },
]

const stockClass = (ingrediente) => {
    return Number(ingrediente.stock) <= Number(ingrediente.min_stock)
        ? "bg-red-50 text-red-700"
        : "bg-emerald-50 text-emerald-700";
};

onMounted(fetchIngredientes);
</script>

<template>
    <section class="grid gap-6 xl:grid-cols-[380px_1fr]">
        <form
            class="rounded-2xl bg-white p-6 shadow-sm"
            @submit.prevent="submit"
        >
            <div class="mb-5">
                <h3 class="text-lg font-bold text-slate-950">
                    {{ isEditing ? "Editar ingrediente" : "Nuevo ingrediente" }}
                </h3>
                <p class="text-sm text-slate-500">
                    Controla stock, unidad y costo base.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Nombre</label
                    >
                    <input
                        v-model="form.name"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Unidad</label
                    >
                    <select
                        v-model="form.unit"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    >
                        <option value="unidad">Unidad</option>
                        <option value="ml">Mililitros</option>
                        <option value="lt">Litros</option>
                        <option value="gr">Gramos</option>
                        <option value="kg">Kilogramos</option>
                        <option value="botella">Botella</option>
                        <option value="ramo">Ramo</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-700"
                            >Stock</label
                        >
                        <input
                            v-model="form.stock"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                        />
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700"
                            >Stock mínimo</label
                        >
                        <input
                            v-model="form.min_stock"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                        />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Costo</label
                    >
                    <input
                        v-model="form.cost"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Estado</label
                    >
                    <select
                        v-model="form.status"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    >
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
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
                <h3 class="text-lg font-bold text-slate-950">Ingredientes</h3>
                <span class="text-sm font-semibold text-slate-400"
                    >{{ ingredientes.length }} registros</span
                >
            </div>

            <ProTable
                :columns="columns"
                :rows="ingredientes"
                :loading="loading"
                empty-message="No hay ingredientes registrados."
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900">{{ row.name }}</span>
                        <span
                            v-if="Number(row.stock) <= Number(row.min_stock) && row.min_stock > 0"
                            class="inline-flex items-center gap-1 rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-bold text-yellow-700"
                        >
                            ⚠ Stock bajo
                        </span>
                    </div>
                </template>

                <template #cell-stock="{ row }">
                    <span
                        class="inline-block rounded-full px-3 py-1 text-xs font-bold"
                        :class="stockClass(row)"
                    >
                        {{ row.stock }} / min {{ row.min_stock }}
                    </span>
                </template>

                <template #cell-cost="{ row }">
                    <span class="font-semibold text-slate-900">
                        {{ formatMoney(row.cost) }}
                    </span>
                </template>

                <template #cell-status="{ row }">
                    <span
                        class="inline-block rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                    >
                        {{ row.status }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <button
                        class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                        @click="editIngrediente(row)"
                    >
                        Editar
                    </button>
                    <button
                        class="rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-600 hover:bg-red-100"
                        @click="deleteIngrediente(row)"
                    >
                        Eliminar
                    </button>
                </template>
            </ProTable>
        </article>
    </section>

    <ToastNotification
        v-if="toast.message"
        :message="toast.message"
        :type="toast.type"
        @close="toast.message = ''"
    />
</template>
