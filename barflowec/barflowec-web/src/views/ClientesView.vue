<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import api from "@/services/api";
import ProTable from "@/components/ProTable.vue";
import ToastNotification from "@/components/ToastNotification.vue";

const clientes = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const error = ref("");
const toast = ref({ message: "", type: "success" });

const columns = [
    { key: "name", label: "Cliente" },
    { key: "email", label: "Contacto" },
    { key: "company", label: "Empresa", class: "hidden md:table-cell" },
    { key: "status", label: "Estado" },
];

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
        toast.value = {
            message: isEditing.value
                ? "Cliente actualizado correctamente."
                : "Cliente creado correctamente.",
            type: "success",
        };
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

            <ProTable
                :columns="columns"
                :rows="clientes"
                :loading="loading"
                empty-message="No hay clientes registrados."
                empty-icon="👤"
            >
                <template #cell-name="{ row }">
                    <div>
                        <p class="font-bold text-slate-900">{{ row.name }}</p>
                        <p class="text-xs text-slate-500">
                            {{ row.identification || "Sin identificación" }}
                        </p>
                    </div>
                </template>
                <template #cell-email="{ row }">
                    <div>
                        <p>{{ row.email || "Sin email" }}</p>
                        <p class="text-xs text-slate-400">
                            {{ row.phone || "Sin teléfono" }}
                        </p>
                    </div>
                </template>
                <template #cell-status="{ row }">
                    <span
                        class="inline-block rounded-full px-3 py-1 text-xs font-bold"
                        :class="
                            row.status === 'activo'
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-red-50 text-red-700'
                        "
                    >
                        {{ row.status }}
                    </span>
                </template>
                <template #actions="{ row }">
                    <button
                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        @click="editCliente(row)"
                    >
                        Editar
                    </button>
                    <button
                        class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100"
                        @click="deleteCliente(row)"
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
