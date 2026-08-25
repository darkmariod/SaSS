<script setup>
import { computed } from "vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    planes: { type: Array, required: true },
    demoUrl: { type: String, default: null },
    whatsapp: { type: String, default: null },
});

const precio = (v) => `$${Number(v ?? 0).toFixed(0)}`;

// El botón de contacto aparece solo si hay un número configurado: un enlace de
// WhatsApp roto en la página de venta cuesta más que no tenerlo.
const whatsappUrl = computed(() => {
    const n = String(props.whatsapp ?? "").replace(/[^0-9]/g, "");
    if (!n) return null;
    return `https://wa.me/${n}?text=${encodeURIComponent(
        "Hola, quiero el sistema de reservas para mi barbería.",
    )}`;
});

const beneficios = [
    {
        titulo: "Tus clientes reservan solos",
        texto: "Compartís un enlace o pegás el código QR en la puerta. El cliente elige servicio, barbero y hora desde su celular, sin escribirte y sin instalar nada.",
    },
    {
        titulo: "La caja cuadra sola",
        texto: "Abrís la caja al empezar el día y la cerrás al terminar. El sistema suma lo cobrado en efectivo, las transferencias y los gastos, y te dice si el cajón cuadra.",
    },
    {
        titulo: "Cada barbero, su agenda",
        texto: "Cada uno entra con su clave y ve solo sus citas del día. Nadie puede tomar un horario ocupado, y las comisiones se calculan solas.",
    },
];

const incluido = [
    "Enlace propio y código QR para tu barbería",
    "Agenda por barbero, sin choques de horario",
    "Caja diaria con cierre y cuadre de efectivo",
    "Cierres semanales y mensuales",
    "Comisiones y pagos a cada barbero",
    "Cobro por transferencia con comprobante",
    "Registro de gastos y venta de productos",
    "Funciona en el celular, sin instalar nada",
];

const preguntas = [
    {
        p: "¿Mis clientes tienen que descargar algo?",
        r: "No. Escanean el código QR o tocan el enlace y reservan desde el navegador del celular. No hay aplicación que instalar ni cuenta que crear.",
    },
    {
        p: "¿Qué pasa si dos clientes eligen la misma hora?",
        r: "No puede pasar. Cuando alguien reserva un horario, ese espacio deja de aparecer para los demás en el mismo instante.",
    },
    {
        p: "¿Puedo seguir atendiendo a quien llega sin cita?",
        r: "Sí. Cargás la cita a mano desde el panel y queda registrada igual, con su cobro y su comisión.",
    },
    {
        p: "¿Necesito saber de computadoras?",
        r: "No. La instalación la hacemos nosotros: cargamos tus servicios, tus barberos y sus horarios, y te entregamos el código QR listo para pegar.",
    },
    {
        p: "¿Y si quiero dejarlo?",
        r: "El primer mes es de prueba. Si no te sirve, no pagás nada y tus datos son tuyos.",
    },
];
</script>

<template>
    <Head title="Sistema de reservas para barberías" />

    <div class="min-h-screen bg-white text-[#111111] dark:bg-[#0E0E0E] dark:text-[#F0EFED]">
        <!-- Portada -->
        <header class="border-b border-[#EAE7E2] dark:border-[#242424]">
            <div class="mx-auto max-w-3xl px-6 py-20 sm:py-28">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#8A8A8A]">
                    Para barberías del Ecuador
                </p>

                <h1 class="mt-6 text-[2.5rem] font-extrabold leading-[1.05] tracking-tight sm:text-[3.5rem]">
                    Dejá de anotar turnos<br />por WhatsApp
                </h1>

                <p class="mt-7 max-w-[38rem] text-lg leading-relaxed text-[#4A4A4A] dark:text-[#B5B2AE]">
                    Tus clientes reservan solos desde el celular. Vos ves la agenda, la caja y
                    lo que gana cada barbero, sin sacar cuentas a fin de mes.
                </p>

                <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                    <a
                        v-if="whatsappUrl"
                        :href="whatsappUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-xl bg-[#111111] px-8 py-4 text-center text-base font-bold text-white transition hover:bg-[#2A2A2A] active:scale-[0.99] dark:bg-white dark:text-[#111111]"
                    >
                        Quiero el sistema
                    </a>
                    <a
                        v-if="demoUrl"
                        :href="demoUrl"
                        class="rounded-xl border border-[#D8D4CE] px-8 py-4 text-center text-base font-bold transition hover:border-[#111111] dark:border-[#333] dark:hover:border-white"
                    >
                        Ver una barbería funcionando
                    </a>
                </div>

                <p v-if="demoUrl" class="mt-5 text-sm text-[#8A8A8A]">
                    Es una barbería real, con sus servicios y horarios. Probá reservar una cita.
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6">
            <!-- Tres beneficios -->
            <section class="grid gap-8 py-16 sm:grid-cols-3 sm:gap-6">
                <div v-for="b in beneficios" :key="b.titulo">
                    <h2 class="text-lg font-bold leading-snug">{{ b.titulo }}</h2>
                    <p class="mt-3 text-[0.95rem] leading-relaxed text-[#4A4A4A] dark:text-[#B5B2AE]">
                        {{ b.texto }}
                    </p>
                </div>
            </section>

            <!-- Qué incluye -->
            <section class="border-t border-[#EAE7E2] py-16 dark:border-[#242424]">
                <h2 class="text-2xl font-extrabold tracking-tight">Qué incluye</h2>

                <ul class="mt-8 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    <li v-for="f in incluido" :key="f" class="flex gap-3">
                        <svg class="mt-[0.3rem] h-4 w-4 shrink-0 text-[#111111] dark:text-white" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5l4 4 8-9" />
                        </svg>
                        <span class="text-[0.95rem] leading-relaxed">{{ f }}</span>
                    </li>
                </ul>
            </section>

            <!-- Precio -->
            <section class="border-t border-[#EAE7E2] py-16 dark:border-[#242424]">
                <h2 class="text-2xl font-extrabold tracking-tight">Precio</h2>
                <p class="mt-3 text-[#4A4A4A] dark:text-[#B5B2AE]">
                    El primer mes es de prueba. La instalación va una sola vez.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div
                        v-for="plan in planes"
                        :key="plan.id"
                        class="rounded-2xl border border-[#EAE7E2] p-6 dark:border-[#242424]"
                    >
                        <h3 class="font-bold">{{ plan.name }}</h3>
                        <p class="mt-1 text-sm text-[#8A8A8A]">Hasta {{ plan.max_barbers }} barberos</p>
                        <p class="mt-5 text-4xl font-extrabold tabular-nums">
                            {{ precio(plan.monthly_price) }}<span class="text-lg font-bold text-[#8A8A8A]">/mes</span>
                        </p>
                        <p class="mt-2 text-sm text-[#8A8A8A]">
                            + {{ precio(plan.setup_price) }} de instalación
                        </p>
                    </div>
                </div>
            </section>

            <!-- Preguntas -->
            <section class="border-t border-[#EAE7E2] py-16 dark:border-[#242424]">
                <h2 class="text-2xl font-extrabold tracking-tight">Preguntas frecuentes</h2>

                <div class="mt-6 divide-y divide-[#EAE7E2] dark:divide-[#242424]">
                    <details v-for="q in preguntas" :key="q.p" class="group py-5">
                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4 font-bold">
                            {{ q.p }}
                            <span class="mt-1 shrink-0 text-[#8A8A8A] transition group-open:rotate-45">+</span>
                        </summary>
                        <p class="mt-3 max-w-[38rem] text-[0.95rem] leading-relaxed text-[#4A4A4A] dark:text-[#B5B2AE]">
                            {{ q.r }}
                        </p>
                    </details>
                </div>
            </section>

            <!-- Cierre -->
            <section class="border-t border-[#EAE7E2] py-16 dark:border-[#242424]">
                <h2 class="text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">
                    Probalo un mes.<br />Si no te sirve, no pagás.
                </h2>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a
                        v-if="whatsappUrl"
                        :href="whatsappUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-xl bg-[#111111] px-8 py-4 text-center text-base font-bold text-white transition hover:bg-[#2A2A2A] dark:bg-white dark:text-[#111111]"
                    >
                        Escribinos por WhatsApp
                    </a>
                    <a
                        v-if="demoUrl"
                        :href="demoUrl"
                        class="rounded-xl border border-[#D8D4CE] px-8 py-4 text-center text-base font-bold transition hover:border-[#111111] dark:border-[#333] dark:hover:border-white"
                    >
                        Ver la demostración
                    </a>
                </div>
            </section>
        </main>

        <footer class="border-t border-[#EAE7E2] py-10 dark:border-[#242424]">
            <p class="mx-auto max-w-3xl px-6 text-sm text-[#8A8A8A]">
                Sistema de reservas y caja para barberías · Riobamba, Ecuador
            </p>
        </footer>
    </div>
</template>
