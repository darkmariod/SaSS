<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import api from "@/services/api";

const clientes = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const error = ref("");

const form = reactive({
    name: "",
    email: "",
    phone: "",
    company: "",
    identification: "",
    address: "",
    notes: "",
    status: "activo",
});

const isEditing = computed(() => Boolean(editingId.value));

const resetForm = () => {
    editingId.value = null;
    form.name = "";
    form.email = "";
    form.phone = "";
    form.company = "";
    form.identification = "";
    form.address = "";
    form.notes = "";
    form.status = "activo";
};

const fetchClientes = async () => {
    loading.value = true;
    const { data } = await api.get("/clientes");
    clientes.value = data.data || data;
    loading.value = false;
};

const submit = async () => {
    saving.value = true;
    error.value = "";

    try {
        if (isEditing.value) {
            await api.put(`/clientes/${editingId.value}`, form);
        } else {
            await api.post("/clientes", form);
        }

        resetForm();
        await fetchClientes();
    } catch (exception) {
        error.value =
            exception.response?.data?.message ||
            "No se pudo guardar el cliente.";
    } finally {
        saving.value = false;
    }
};

const editCliente = (cliente) => {
    editingId.value = cliente.id;
    form.name = cliente.name || "";
    form.email = cliente.email || "";
    form.phone = cliente.phone || "";
    form.company = cliente.company || "";
    form.identification = cliente.identification || "";
    form.address = cliente.address || "";
    form.notes = cliente.notes || "";
    form.status = cliente.status || "activo";
};

const deleteCliente = async (cliente) => {
    if (!confirm(`Eliminar cliente ${cliente.name}?`)) return;

    await api.delete(`/clientes/${cliente.id}`);
    await fetchClientes();
};

onMounted(fetchClientes);
</script>

<template>
    <section class="grid gap-6 xl:grid-cols-[380px_1fr]">
        <form
            class="rounded-2xl bg-white p-6 shadow-sm"
            @submit.prevent="submit"
        >
            <div class="mb-5">
                <h3 class="text-lg font-bold text-slate-950">
                    {{ isEditing ? "Editar cliente" : "Nuevo cliente" }}
                </h3>
                <p class="text-sm text-slate-500">
                    Registra información comercial básica.
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
                        >Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Teléfono</label
                    >
                    <input
                        v-model="form.phone"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Empresa</label
                    >
                    <input
                        v-model="form.company"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Identificación</label
                    >
                    <input
                        v-model="form.identification"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Dirección</label
                    >
                    <textarea
                        v-model="form.address"
                        rows="2"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-purple-500"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700"
                        >Notas</label
                    >
                    <textarea
                        v-model="form.notes"
                        rows="2"
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
                <h3 class="text-lg font-bold text-slate-950">Clientes</h3>
                <span class="text-sm font-semibold text-slate-400"
                    >{{ clientes.length }} registros</span
                >
            </div>

            <div v-if="loading" class="py-10 text-center text-slate-500">
                Cargando clientes...
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-500">
                            <th class="py-3 font-semibold">Cliente</th>
                            <th class="py-3 font-semibold">Contacto</th>
                            <th class="py-3 font-semibold">Empresa</th>
                            <th class="py-3 font-semibold">Estado</th>
                            <th class="py-3 text-right font-semibold">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="cliente in clientes"
                            :key="cliente.id"
                            class="border-b border-slate-50"
                        >
                            <td class="py-4">
                                <p class="font-bold text-slate-900">
                                    {{ cliente.name }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{
                                        cliente.identification ||
                                        "Sin identificación"
                                    }}
                                </p>
                            </td>
                            <td class="py-4 text-slate-600">
                                <p>{{ cliente.email || "Sin email" }}</p>
                                <p class="text-xs">
                                    {{ cliente.phone || "Sin teléfono" }}
                                </p>
                            </td>
                            <td class="py-4 text-slate-600">
                                {{ cliente.company || "-" }}
                            </td>
                            <td class="py-4">
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                                >
                                    {{ cliente.status }}
                                </span>
                            </td>
                            <td class="py-4 text-right">
                                <button
                                    class="mr-2 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                                    @click="editCliente(cliente)"
                                >
                                    Editar
                                </button>
                                <button
                                    class="rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-600 hover:bg-red-100"
                                    @click="deleteCliente(cliente)"
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
