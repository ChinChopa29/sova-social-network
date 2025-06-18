import { createRouter, createWebHistory } from "vue-router";
import Home from "./views/Home.vue";
import MainLayout from "./components/layouts/MainLayout.vue";
import Login from "./views/auth/Login.vue";
import Register from "./views/auth/Register.vue";
import NotFound from "./views/NotFound.vue";
import Profile from "./views/profile/Profile.vue";
import ProfileEdit from "./views/profile/ProfileEdit.vue";
import { useAuthStore } from "./store/auth.js";

const routes = [
  {
    path: "/",
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: "",
        name: "Home",
        component: Home,
      },
      {
        path: "/profile/:slug",
        name: "Profile",
        component: Profile,
      },
      {
        path: "/profile/:slug/edit",
        name: "ProfileEdit",
        component: ProfileEdit,
        meta: { requiresOwnProfile: true }, // 👈 проверка на владельца
      },
    ],
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
    meta: { guestOnly: true }, // 👈 только для неавторизованных
  },
  {
    path: "/register",
    name: "Register",
    component: Register,
    meta: { guestOnly: true },
  },
  {
    path: "/:pathMatch(.*)*",
    name: "NotFound",
    component: NotFound,
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  if (!authStore.user) {
    try {
      await authStore.getUser();
    } catch (e) {}
  }

  const isLoggedIn = !!authStore.user;

  if (to.meta.requiresAuth && !isLoggedIn) {
    return next({ name: "Login" });
  }

  if (to.meta.guestOnly && isLoggedIn) {
    return next({ name: "Home" });
  }

  if (to.meta.requiresOwnProfile) {
    const ownSlug = authStore.user?.profile?.slug;
    if (to.params.slug !== ownSlug) {
      return next({ name: "NotFound" });
    }
  }

  next();
});

export default router;
