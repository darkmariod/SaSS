<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import api from "@/services/api";

const paquetes = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const error = ref("");

const form = reactive({
    name: "",
    description: "",
    guests_min: 1,
    guests_max: 1,
    price: 0,
    status: "activo",
});

const isEditing = computed(() => Boolean(editingId.value));

const resetForm = () => {
    editingId.value = null;
    form.name = "";
    form.description = "";
    form.guests_min = 1;
    form.guests_max = 1;
    form.price = 0;
    form.status = "activo";
};

const fetchPaquetes = async () => {
    loading.value = true;
    const { data } = await api.get("/paquetes");
    paquetes.value = data.data || data;
    loading.value = false;
};

const submit = async () => {
    saving.value = true;
    error.value = "";

    try {
        const payload = {
            ...form,
            guests_min: Number(form.guests_min),
            guests_max: Number(form.guests_max),
            price: Number(form.price),
        };

        if (isEditing.value) {
            await api.put(`/paquetes/${editingId.value}`, payload);
        } else {
            await api.post("/paquetes", payload);
        }

        resetForm();
        await fetchPaquetes();
    } catch (exception) {
        error.value =
            exception.response?.data?.message ||
            "No se pudo guardar el paquete.";
    } finally {
        saving.value = false;
    }
};

const editPaquete = (paquete) => {
    editingId.value = paquete.id;
    form.name = paquete.name || "";
    form.description = paquete.description || "";
    form.guests_min = paquete.guests_min || 1;
    form.guests_max = paquete.guests_max || 1;
    form.price = paquete.price || 0;
    form.status = paquete.status || "activo";
};

const deletePaquete = async (paquete) => {
    if (!confirm(`Eliminar paquete ${paquete.name}?`)) return;

    await api.delete(`/paquetes/${paquete.id}`);
    await fetchPaquetes();
};

onMounted(fetchPaquetes);
</script>

<template>
    <section class="grid gap-6 xl:grid-cols-[420px_1fr]">
        <form
            class="rounded-2xl bg-white p-6 shadow-sm"
            @submit.prevent="submit"
        >
            <div class="mb-5">
                <h3 class="text-lg font-bold text-slate-950">
                    {{ isEditing ? "Editar paquete" : "Nuevo paquete" }}
                </h3>
                <p class="text-sm text-slate-500">
                    Crea ofertas por rango de invitados.
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
                        rows="4"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-700"
                            >Mín. invitados</label
                        >
                        <input
                            v-model="form.guests_min"
                            type="number"
                            min="1"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                        />
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700"
                            >Máx. invitados</label
                        >
                        <input
                            v-model="form.guests_max"
                            type="number"
                            min="1"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                        />
                    </div>
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
                <h3 class="text-lg font-bold text-slate-950">Paquetes</h3>
                <span class="text-sm font-semibold text-slate-400"
                    >{{ paquetes.length }} registros</span
                >
            </div>

            <div v-if="loading" class="py-10 text-center text-slate-500">
                Cargando paquetes...
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-500">
                            <th class="py-3 font-semibold">Paquete</th>
                            <th class="py-3 font-semibold">Invitados</th>
                            <th class="py-3 font-semibold">Precio</th>
                            <th class="py-3 font-semibold">Estado</th>
                            <th class="py-3 text-right font-semibold">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="paquete in paquetes"
                            :key="paquete.id"
                            class="border-b border-slate-50"
                        >
                            <td class="py-4">
                                <p class="font-bold text-slate-900">
                                    {{ paquete.name }}
                                </p>
                                <p
                                    class="max-w-md truncate text-xs text-slate-500"
                                >
                                    {{
                                        paquete.description || "Sin descripción"
                                    }}
                                </p>
                            </td>
                            <td class="py-4 text-slate-600">
                                {{ paquete.guests_min }} -
                                {{ paquete.guests_max }}
                            </td>
                            <td class="py-4 font-semibold text-slate-900">
                                ${{ Number(paquete.price).toFixed(2) }}
                            </td>
                            <td class="py-4">
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                                >
                                    {{ paquete.status }}
                                </span>
                            </td>
                            <td class="py-4 text-right">
                                <button
                                    class="mr-2 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                                    @click="editPaquete(paquete)"
                                >
                                    Editar
                                </button>
                                <button
                                    class="rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-600 hover:bg-red-100"
                                    @click="deletePaquete(paquete)"
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
