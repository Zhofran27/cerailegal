import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";
import HomeView from "../views/HomeView.vue";
import TransparencyView from "../views/TransparencyView.vue";
import ServicesView from "../views/ServicesView.vue";
import EligibilityView from "../views/EligibilityView.vue";
import InfoView from "../views/InfoView.vue";
import PublicDataView from "../views/PublicDataView.vue";
import AuthView from "../views/AuthView.vue";
import DashboardView from "../views/DashboardView.vue";
import CaseCreateView from "../views/CaseCreateView.vue";
import CaseDetailView from "../views/CaseDetailView.vue";

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    { path: "/", component: HomeView },
    { path: "/home", redirect: "/" },
    { path: "/eligibility", component: EligibilityView },
    { path: "/services", component: ServicesView },
    { path: "/workflow", component: InfoView, props: { page: "workflow" } },
    { path: "/transparency", component: TransparencyView },
    { path: "/mediation", component: InfoView, props: { page: "mediation" } },
    { path: "/privacy", component: InfoView, props: { page: "privacy" } },
    { path: "/faqs", component: PublicDataView, props: { page: "faqs" } },
    { path: "/courts", component: PublicDataView, props: { page: "courts" } },
    { path: "/status", component: PublicDataView, props: { page: "status" } },
    {
      path: "/cost-estimate",
      component: PublicDataView,
      props: { page: "cost" },
    },
    { path: "/login", component: AuthView, props: { mode: "login" } },
    { path: "/register", component: AuthView, props: { mode: "register" } },
    { path: "/dashboard", component: DashboardView, meta: { auth: true } },
    { path: "/cases/create", component: CaseCreateView, meta: { auth: true } },
    { path: "/cases/:id", component: CaseDetailView, meta: { auth: true } },
  ],
});

router.beforeEach((to) => {
  const auth = useAuthStore();
  if (to.meta.auth && !auth.authenticated)
    return { path: "/login", query: { redirect: to.fullPath } };
  if ((to.path === "/login" || to.path === "/register") && auth.authenticated)
    return "/dashboard";
});

export default router;
