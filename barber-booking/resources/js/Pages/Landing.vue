<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    planes: { type: Array, required: true },
    demoUrl: { type: String, default: null },
    whatsapp: { type: String, default: null },
});

const precio = (v) => `$${Number(v ?? 0).toFixed(0)}`;

const whatsappUrl = props.whatsapp
    ? `https://wa.me/${String(props.whatsapp).replace(/[^0-9]/g, "")}?text=${encodeURIComponent(
          "Hola, quiero el sistema de reservas para mi barbería.",
      )}`
    : null;

/** Lo que hoy hace el dueño a mano, y lo que pasa a hacer el sistema. */
const cambios = [
    {
        antes: "Los turnos llegan por WhatsApp a cualquier hora",
        despues: "El cliente reserva solo, desde su celular, sin escribirte",
    },
    {
        antes: "La agenda vive en un cuaderno o en la cabeza",
        despues: "Cada barbero ve sus citas del día en su teléfono",
    },
    {
        antes: "Al cerrar hay que sacar cuentas de memoria",
        despues: "La caja cuadra sola: efectivo, transferencias y gastos",
    },
    {
        antes: "Las comisiones se calculan a fin de mes con calculadora",
        despues: "El sistema dice cuánto le toca a cada barbero",
    },
];
</script>

<template>
    <Head title="Sistema de reservas para barberías" />

    <div class="min-h-screen bg-[#F7F4EF] text-[#17130E] dark:bg-[#12100D] dark:text-[#F2EBE0]">
        <!-- Portada -->
        <header class="bg-[#17130E] text-[#F2EBE0]">
            <div class="mx-auto max-w-3xl px-6 pb-16 pt-16 sm:pt-24">
                <p class="text-[0.68rem] font-bold uppercase tracking-[0.32em] text-[#C9A24C]">
                    Para barberías del Ecuador
                </p>

                <h1 class="mt-5 text-[2.4rem] font-black uppercase leading-[0.95] tracking-tight sm:text-6xl">
                    Tu barbería con<br />agenda propia
                </h1>

                <p class="mt-6 max-w-prose text-lg leading-relaxed text-[#CFC5B5]">
                    Tus clientes reservan solos desde el celular. Vos ves la agenda, la caja
                    y las comisiones de cada barbero en un solo lugar.
                </p>

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a
                        v-if="demoUrl"
                        :href="demoUrl"
                        class="rounded-lg bg-[#C9A24C] px-7 py-4 text-center text-base font-black uppercase tracking-wide text-[#17130E] transition active:scale-[0.99]"
                    >
                        Ver una barbería funcionando
                    </a>
                    <Link
                        :href="route('register')"
                        class="rounded-lg border border-[#4A4036] px-7 py-4 text-center text-base font-bold text-[#F2EBE0] transition hover:border-[#C9A24C] hover:text-[#C9A24C]"
                    >
                        Crear mi cuenta
                    </Link>
                </div>

                <p v-if="demoUrl" class="mt-4 text-sm text-[#8A7F70]">
                    Es una barbería de verdad, con sus servicios y horarios. Probá reservar.
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6">
            <!-- Antes / después: el argumento de venta -->
            <section class="pt-14">
                <h2 class="border-b-2 border-[#17130E] pb-2 text-xl font-black uppercase tracking-tight dark:border-[#C9A24C]">
                    Qué cambia en tu día
                </h2>

                <ul class="divide-y divide-[#E2D9CA] dark:divide-[#2A241C]">
                    <li v-for="(c, i) in cambios" :key="i" class="grid gap-1 py-5 sm:grid-cols-2 sm:gap-6">
                        <p class="text-[#8A7F70] line-through decoration-[#C6BAA6]">{{ c.antes }}</p>
                        <p class="font-semibold">{{ c.despues }}</p>
                    </li>
                </ul>
            </section>

            <!-- Precios reales del sistema -->
            <section class="pt-14">
                <h2 class="border-b-2 border-[#17130E] pb-2 text-xl font-black uppercase tracking-tight dark:border-[#C9A24C]">
                    Precio
                </h2>

                <div class="grid gap-3 pt-6 sm:grid-cols-3">
                    <div
                        v-for="plan in planes"
                        :key="plan.id"
                        class="rounded-lg border border-[#E2D9CA] bg-white p-5 dark:border-[#2A241C] dark:bg-[#1A1712]"
                    >
                        <h3 class="font-black uppercase tracking-tight">{{ plan.name }}</h3>
                        <p class="mt-1 text-sm text-[#7A7063] dark:text-[#9C9184]">
                            Hasta {{ plan.max_barbers }} barberos
                        </p>
                        <p class="mt-4 text-3xl font-black tabular-nums">
                            {{ precio(plan.monthly_price) }}<span class="text-base font-bold text-[#8A7F70]">/mes</span>
                        </p>
                        <p class="mt-1 text-sm text-[#8A7F70]">
                            + {{ precio(plan.setup_price) }} de instalación, una sola vez
                        </p>
                    </div>
                </div>

                <p class="pt-5 text-sm leading-relaxed text-[#7A7063] dark:text-[#9C9184]">
                    El primer mes es de prueba. La instalación incluye cargar tus servicios,
                    tus barberos y sus horarios, y dejarte el código QR listo para pegar en el local.
                </p>
            </section>

            <!-- Qué incluye -->
            <section class="pt-14">
                <h2 class="border-b-2 border-[#17130E] pb-2 text-xl font-black uppercase tracking-tight dark:border-[#C9A24C]">
                    Todo incluido
                </h2>

                <ul class="grid gap-x-6 gap-y-3 pt-6 sm:grid-cols-2">
                    <li v-for="f in [
                        'Reservas online con tu propio enlace y código QR',
                        'Agenda por barbero, sin choques de horario',
                        'Caja diaria con cierre y cuadre de efectivo',
                        'Cierres semanales y mensuales',
                        'Comisiones y pagos a cada barbero',
                        'Cobro por transferencia con comprobante',
                        'Cada barbero entra con su propia clave',
                        'Funciona en el celular, sin instalar nada',
                    ]" :key="f" class="flex gap-3">
                        <span class="mt-[0.55rem] h-[0.4rem] w-[0.4rem] shrink-0 rounded-full bg-[#C9A24C]" />
                        <span>{{ f }}</span>
                    </li>
                </ul>
            </section>

            <!-- Cierre -->
            <section class="py-14">
                <div class="rounded-xl bg-[#17130E] px-6 py-10 text-[#F2EBE0]">
                    <h2 class="text-2xl font-black uppercase leading-tight tracking-tight">
                        Empezá con el mes de prueba
                    </h2>
                    <p class="mt-3 max-w-prose text-[#CFC5B5]">
                        Creás tu cuenta, cargamos tu barbería y te dejamos el QR listo.
                        Si no te sirve, no pagás nada.
                    </p>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="route('register')"
                            class="rounded-lg bg-[#C9A24C] px-7 py-4 text-center font-black uppercase tracking-wide text-[#17130E]"
                        >
                            Crear mi cuenta
                        </Link>
                        <a
                            v-if="whatsappUrl"
                            :href="whatsappUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="rounded-lg border border-[#4A4036] px-7 py-4 text-center font-bold transition hover:border-[#C9A24C] hover:text-[#C9A24C]"
                        >
                            Escribinos por WhatsApp
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
