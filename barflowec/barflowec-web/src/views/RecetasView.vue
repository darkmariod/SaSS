<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import api from "@/services/api";
import ProTable from '@/components/ProTable.vue'
import ToastNotification from '@/components/ToastNotification.vue'

const recetas = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const error = ref("");
const toast = ref({ message: '', type: 'success' })
const clearToast = () => { toast.value = { message: '', type: 'success' } }

const form = reactive({
    name: "",
    description: "",
    preparation: "",
    servings: 1,
    price: 0,
    status: "activa",
});

const isEditing = computed(() => Boolean(editingId.value));

const resetForm = () => {
    editingId.value = null;
    form.name = "";
    form.description = "";
    form.preparation = "";
    form.servings = 1;
    form.price = 0;
    form.status = "activa";
};

const fetchRecetas = async () => {
    loading.value = true;
    const { data } = await api.get("/recetas");
    recetas.value = data.data || data;
    loading.value = false;
};

const submit = async () => {
    saving.value = true;
    error.value = "";

    try {
        const payload = {
            ...form,
            servings: Number(form.servings),
            price: Number(form.price),
        };

        if (isEditing.value) {
            await api.put(`/recetas/${editingId.value}`, payload);
        } else {
            await api.post("/recetas", payload);
        }

        resetForm();
        await fetchRecetas();
        toast.value = { message: 'Receta guardada correctamente.', type: 'success' }
    } catch (exception) {
        error.value =
            exception.response?.data?.message ||
            "No se pudo guardar la receta.";
    } finally {
        saving.value = false;
    }
};

const editReceta = (receta) => {
    editingId.value = receta.id;
    form.name = receta.name || "";
    form.description = receta.description || "";
    form.preparation = receta.preparation || "";
    form.servings = receta.servings || 1;
    form.price = receta.price || 0;
    form.status = receta.status || "activa";
};

const deleteReceta = async (receta) => {
    if (!confirm(`Eliminar receta ${receta.name}?`)) return;

    await api.delete(`/recetas/${receta.id}`);
    await fetchRecetas();
};

const formatMoney = (value) => {
    return `$${Number(value).toFixed(2)}`
}

const columns = [
    { key: 'name', label: 'Servicio' },
    { key: 'description', label: 'Descripción', class: 'hidden md:table-cell' },
    { key: 'price', label: 'Precio', align: 'right' },
    { key: 'status', label: 'Estado' },
]

onMounted(fetchRecetas);
</script>

<template>
    <section class="grid gap-6 xl:grid-cols-[420px_1fr]">
        <form
            class="rounded-2xl bg-white p-6 shadow-sm"
            @submit.prevent="submit"
        >
            <div class="mb-5">
                <h3 class="text-lg font-bold text-slate-950">
                    {{ isEditing ? "Editar receta" : "Nueva receta" }}
                </h3>
                <p class="text-sm text-slate-500">
                    Define bebidas, porciones y precio base.
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
                        >Descripción</label
                    >
                    <textarea
                        v-model="form.description"
                        rows="2"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Preparación</label
                    >
                    <textarea
                        v-model="form.preparation"
                        rows="4"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-700"
                            >Porciones</label
                        >
                        <input
                            v-model="form.servings"
                            type="number"
                            min="1"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                        />
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700"
                            >Precio</label
                        >
                        <input
                            v-model="form.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                        />
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
                        <option value="activa">Activa</option>
                        <option value="inactiva">Inactiva</option>
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
                <h3 class="text-lg font-bold text-slate-950">Recetas</h3>
                <span class="text-sm font-semibold text-slate-400"
                    >{{ recetas.length }} registros</span
                >
            </div>

            <ProTable
                :columns="columns"
                :rows="recetas"
                :loading="loading"
                empty-message="No hay recetas registradas."
            >
                <template #cell-name="{ row }">
                    <p class="font-bold text-slate-900">{{ row.name }}</p>
                    <p class="max-w-md truncate text-xs text-slate-500">
                        {{ row.description || "Sin descripción" }}
                    </p>
                </template>

                <template #cell-description="{ row }">
                    {{ row.description || "Sin descripción" }}
                </template>

                <template #cell-price="{ row }">
                    <span class="font-semibold text-slate-900">{{ formatMoney(row.price) }}</span>
                </template>

                <template #cell-status="{ row }">
                    <span
                        class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                    >
                        {{ row.status }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <button
                        class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                        @click="editReceta(row)"
                    >
                        Editar
                    </button>
                    <button
                        class="rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-600 hover:bg-red-100"
                        @click="deleteReceta(row)"
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
        @close="clearToast"
    />
</template>
