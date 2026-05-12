<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import api from "@/services/api";

const ingredientes = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const error = ref("");

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
};

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

            <div v-if="loading" class="py-10 text-center text-slate-500">
                Cargando ingredientes...
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-500">
                            <th class="py-3 font-semibold">Ingrediente</th>
                            <th class="py-3 font-semibold">Unidad</th>
                            <th class="py-3 font-semibold">Stock</th>
                            <th class="py-3 font-semibold">Costo</th>
                            <th class="py-3 font-semibold">Estado</th>
                            <th class="py-3 text-right font-semibold">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="ingrediente in ingredientes"
                            :key="ingrediente.id"
                            class="border-b border-slate-50"
                        >
                            <td class="py-4 font-bold text-slate-900">
                                {{ ingrediente.name }}
                            </td>
                            <td class="py-4 text-slate-600">
                                {{ ingrediente.unit }}
                            </td>
                            <td class="py-4">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold"
                                    :class="stockClass(ingrediente)"
                                >
                                    {{ ingrediente.stock }} / min
                                    {{ ingrediente.min_stock }}
                                </span>
                            </td>
                            <td class="py-4 font-semibold text-slate-900">
                                ${{ ingrediente.cost }}
                            </td>
                            <td class="py-4">
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                                >
                                    {{ ingrediente.status }}
                                </span>
                            </td>
                            <td class="py-4 text-right">
                                <button
                                    class="mr-2 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                                    @click="editIngrediente(ingrediente)"
                                >
                                    Editar
                                </button>
                                <button
                                    class="rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-600 hover:bg-red-100"
                                    @click="deleteIngrediente(ingrediente)"
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
