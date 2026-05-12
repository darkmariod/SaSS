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
            title: "Dashboard",
            subtitle: "Resumen operativo de BarFlowEC",
          },
        },
        {
          path: "clientes",
          name: "clientes",
          component: ClientesView,
          meta: {
            title: "Clientes",
            subtitle: "Gestión comercial de clientes",
          },
        },
        {
          path: "recetas",
          name: "recetas",
          component: RecetasView,
          meta: {
            title: "Recetas",
            subtitle: "Catálogo de bebidas y preparación",
          },
        },
        {
          path: "ingredientes",
          name: "ingredientes",
          component: IngredientesView,
          meta: {
            title: "Ingredientes",
            subtitle: "Inventario base para recetas y paquetes",
          },
        },
        {
          path: "paquetes",
          name: "paquetes",
          component: PaquetesView,
          meta: {
            title: "Paquetes",
            subtitle: "Ofertas comerciales para eventos",
          },
        },
        {
          path: "cotizaciones",
          name: "cotizaciones",
          component: CotizacionesView,
          meta: {
            title: "Cotizaciones",
            subtitle: "Flujo comercial de propuestas para eventos",
          },
        },
        {
          path: "eventos",
          name: "eventos",
          component: EventosView,
          meta: {
            title: "Eventos",
            subtitle: "Agenda operativa de servicios contratados",
          },
        },
        {
          path: "pagos",
          name: "pagos",
          component: PagosView,
          meta: {
            title: "Pagos",
            subtitle: "Control de cobros, anticipos y saldos",
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
