import { createRouter, createWebHistory } from "vue-router";

import AdminDashboard from "./pages/admin/dashboard/Index.vue";
import AdminPosts from "./pages/admin/posts/Index.vue";
import AdminCategories from "./pages/admin/categories/Index.vue";
import AdminCategoryShow from "./pages/admin/categories/Show.vue";
import AdminTags from "./pages/admin/tags/Index.vue";
import AdminTagShow from "./pages/admin/tags/Show.vue";

import PostView from "./pages/post/Show.vue";

import Home from "./pages/Home.vue";
import MainLayout from "./layouts/MainLayout.vue";
import AdminLayout from "./layouts/AdminLayout.vue";
import Login from "./pages/auth/Login.vue";
import Register from "./pages/auth/Register.vue";
import NotFound from "./pages/NotFound.vue";
import Profile from "./pages/profile/Index.vue";
import ProfileEdit from "./pages/profile/Edit.vue";
import { useAuthStore } from "./store/auth.js";

const routes = [
  {
    path: "/admin-panel",
    component: AdminLayout,
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: "",
        name: "AdminDashboard",
        component: AdminDashboard,
      },
      {
        path: "/admin-panel/posts",
        name: "AdminPosts",
        component: AdminPosts,
      },
      {
        path: "/admin-panel/categories",
        name: "AdminCategories",
        component: AdminCategories,
      },
      {
        path: "/admin-panel/categories/:slug",
        name: "AdminCategoryShow",
        component: AdminCategoryShow,
      },
      {
        path: "/admin-panel/tags",
        name: "AdminTags",
        component: AdminTags,
      },
      {
        path: "/admin-panel/tags/:slug",
        name: "AdminTagShow",
        component: AdminTagShow,
      },
    ],
  },
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
        meta: { requiresOwnProfile: true },
      },
      {
        path: "/posts/:slug",
        name: "posts.show",
        component: PostView,
        props: true,
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

  if (to.meta.requiresAdmin) {
    const role = authStore.user?.role?.trim?.();
    if (role !== "admin") {
      return next({ name: "NotFound" });
    }
  }

  next();
});

export default router;
