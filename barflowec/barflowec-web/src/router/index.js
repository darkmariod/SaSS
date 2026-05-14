import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import LoginView from "@/views/LoginView.vue";
import DashboardView from "@/views/DashboardView.vue";
import ClientesView from "@/views/ClientesView.vue";
import IngredientesView from "@/views/IngredientesView.vue";
import RecetasView from "@/views/RecetasView.vue";
import PaquetesView from "@/views/PaquetesView.vue";
import CotizacionesView from "@/views/CotizacionesView.vue";
import EventosView from "@/views/EventosView.vue";
import PagosView from "@/views/PagosView.vue";
import AdminLayout from "@/layouts/AdminLayout.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/login",
      name: "login",
      component: LoginView,
    },
    {
      path: "/",
      component: AdminLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: "",
          name: "dashboard",
          component: DashboardView,
          meta: {
            title: "Panel comercial",
            subtitle:
              "Clientes, propuestas, agenda, recursos y cobros en un solo panel.",
          },
        },
        {
          path: "clientes",
          name: "clientes",
          component: ClientesView,
          meta: {
            title: "Clientes",
            subtitle: "Contactos y empresas que solicitan eventos.",
          },
        },
        {
          path: "recetas",
          name: "recetas",
          component: RecetasView,
          meta: {
            title: "Servicios",
            subtitle: "Servicios que puedes incluir en paquetes y propuestas.",
          },
        },
        {
          path: "ingredientes",
          name: "ingredientes",
          component: IngredientesView,
          meta: {
            title: "Recursos / Inventario",
            subtitle:
              "Insumos, equipos y recursos necesarios para operar eventos.",
          },
        },
        {
          path: "paquetes",
          name: "paquetes",
          component: PaquetesView,
          meta: {
            title: "Paquetes",
            subtitle:
              "Planes comerciales por tipo de evento, invitados y servicio.",
          },
        },
        {
          path: "cotizaciones",
          name: "cotizaciones",
          component: CotizacionesView,
          meta: {
            title: "Propuestas",
            subtitle: "Cotizaciones comerciales enviadas a clientes.",
          },
        },
        {
          path: "eventos",
          name: "eventos",
          component: EventosView,
          meta: {
            title: "Agenda",
            subtitle: "Eventos confirmados, por confirmar y finalizados.",
          },
        },
        {
          path: "pagos",
          name: "pagos",
          component: PagosView,
          meta: {
            title: "Cobros",
            subtitle: "Anticipos, pagos realizados y saldos pendientes.",
          },
        },
      ],
    },
  ],
});

router.beforeEach((to) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: "login" };
  }

  if (to.name === "login" && auth.isAuthenticated) {
    return { name: "dashboard" };
  }
});

export default router;
