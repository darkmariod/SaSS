<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import api from "@/services/api";

const recetas = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const error = ref("");

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

            <div v-if="loading" class="py-10 text-center text-slate-500">
                Cargando recetas...
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-500">
                            <th class="py-3 font-semibold">Receta</th>
                            <th class="py-3 font-semibold">Porciones</th>
                            <th class="py-3 font-semibold">Precio</th>
                            <th class="py-3 font-semibold">Estado</th>
                            <th class="py-3 text-right font-semibold">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="receta in recetas"
                            :key="receta.id"
                            class="border-b border-slate-50"
                        >
                            <td class="py-4">
                                <p class="font-bold text-slate-900">
                                    {{ receta.name }}
                                </p>
                                <p
                                    class="max-w-md truncate text-xs text-slate-500"
                                >
                                    {{
                                        receta.description || "Sin descripción"
                                    }}
                                </p>
                            </td>
                            <td class="py-4 text-slate-600">
                                {{ receta.servings }}
                            </td>
                            <td class="py-4 font-semibold text-slate-900">
                                ${{ Number(receta.price).toFixed(2) }}
                            </td>
                            <td class="py-4">
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                                >
                                    {{ receta.status }}
                                </span>
                            </td>
                            <td class="py-4 text-right">
                                <button
                                    class="mr-2 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                                    @click="editReceta(receta)"
                                >
                                    Editar
                                </button>
                                <button
                                    class="rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-600 hover:bg-red-100"
                                    @click="deleteReceta(receta)"
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
