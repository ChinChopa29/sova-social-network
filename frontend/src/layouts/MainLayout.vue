<script setup>
import {
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
} from "@headlessui/vue";
import { Bars3Icon, BellIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import router from "../router";
import { computed, ref, onMounted, watch } from "vue";
import axiosClient from "../axios";
import { useAuthStore } from "../store/auth";
import { RouterLink } from "vue-router";

const authStore = useAuthStore();
const user = computed(() => authStore.user);

const isAuthenticated = computed(() => {
  return !!user.value && !!user.value.name;
});

const backendUrl = import.meta.env.VITE_BACKEND_URL;

const getAvatarUrl = (avatar) => {
  return avatar ? `${backendUrl}${avatar}` : "/img/default-avatar.jpg";
};

const navigation = [{ name: "Главная", to: { name: "Home" }, current: true }];

const userNavigation = ref([]);

onMounted(async () => {
  await authStore.getUser();
  buildMenu();
});

watch(user, () => buildMenu(), { immediate: true });

function buildMenu() {
  const items = [
    {
      name: "Профиль",
      onclick: () =>
        router.push({
          name: "Profile",
          params: { slug: authStore.user.profile.slug },
        }),
    },
    { name: "Настройки", href: "#" },
  ];

  if (authStore.user?.role === "admin") {
    items.push({
      name: "Админ-панель",
      onclick: () => router.push({ name: "AdminDashboard" }),
    });
  }

  items.push({ name: "Выйти", onclick: logout });

  userNavigation.value = items;
}

function logout() {
  const authStore = useAuthStore();

  axiosClient.post("/api/logout").then(() => {
    authStore.user = null;
    router.push({ name: "Login" });
  });
}
</script>

<template>
  <div class="min-h-full bg-gray-50">
    <!-- Навигационная панель -->
    <Disclosure
      as="nav"
      class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-lg"
      v-slot="{ open }">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <!-- Лого + навигация -->
          <div class="flex items-center">
            <div class="shrink-0">
              <img class="size-10" src="/img/owl.png" alt="Your Company" />
            </div>
            <div class="hidden md:block">
              <div class="ml-10 flex items-baseline space-x-4">
                <RouterLink
                  v-for="item in navigation"
                  :key="item.name"
                  :to="item.to"
                  :class="[
                    $route.name === item.to.name
                      ? 'bg-blue-700 text-white'
                      : 'text-blue-100 hover:bg-blue-700 hover:text-white',
                    'rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200',
                  ]"
                  :aria-current="
                    $route.name === item.to.name ? 'page' : undefined
                  ">
                  {{ item.name }}
                </RouterLink>
              </div>
            </div>
          </div>

          <!-- Правая часть шапки -->
          <div class="hidden md:flex items-center gap-4">
            <template v-if="isAuthenticated">
              <button
                type="button"
                class="relative rounded-full bg-blue-600 p-1 text-blue-100 hover:text-white focus:outline-none focus:ring-2 focus:ring-white hover:bg-blue-800 transition-colors duration-200">
                <span class="sr-only">Уведомления</span>
                <BellIcon class="size-6 cursor-pointer" aria-hidden="true" />
              </button>

              <!-- Меню пользователя -->
              <Menu as="div" class="relative ml-3">
                <div>
                  <MenuButton
                    class="flex items-center gap-2 max-w-xs rounded-full bg-blue-700 py-1 pr-3 pl-1 text-sm text-white hover:bg-blue-800 transition-colors duration-200 cursor-pointer">
                    <img
                      :src="
                        user?.profile?.image_url || '/img/default-avatar.jpg'
                      "
                      alt="User avatar"
                      class="size-8 rounded-full" />
                    <span class="hidden sm:block">{{ user.name }}</span>
                  </MenuButton>
                </div>
                <transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95">
                  <MenuItems
                    class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none">
                    <MenuItem
                      v-for="item in userNavigation"
                      :key="item.name"
                      v-slot="{ active }">
                      <a
                        @click.prevent="item.onclick ? item.onclick() : null"
                        href="#"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'block px-4 py-2 text-sm text-gray-700',
                        ]">
                        {{ item.name }}
                      </a>
                    </MenuItem>
                  </MenuItems>
                </transition>
              </Menu>
            </template>

            <template v-else>
              <!-- Кнопки входа/регистрации -->
              <RouterLink
                to="/login"
                class="text-white bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Вход
              </RouterLink>
              <RouterLink
                to="/register"
                class="text-white bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Регистрация
              </RouterLink>
            </template>
          </div>

          <!-- Кнопка мобильного меню -->
          <div class="-mr-2 flex md:hidden">
            <DisclosureButton
              class="inline-flex items-center justify-center rounded-md bg-blue-700 p-2 text-blue-100 hover:text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-white">
              <span class="sr-only">Открыть меню</span>
              <Bars3Icon v-if="!open" class="block size-6" aria-hidden="true" />
              <XMarkIcon v-else class="block size-6" aria-hidden="true" />
            </DisclosureButton>
          </div>
        </div>
      </div>

      <!-- Мобильное меню -->
      <DisclosurePanel class="md:hidden bg-blue-700">
        <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
          <RouterLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.to"
            :class="[
              $route.name === item.to.name
                ? 'bg-blue-800 text-white'
                : 'text-blue-100 hover:bg-blue-800 hover:text-white',
              'block rounded-md px-3 py-2 text-base font-medium transition-colors duration-200',
            ]"
            :aria-current="$route.name === item.to.name ? 'page' : undefined">
            {{ item.name }}
          </RouterLink>
        </div>
        <div class="border-t border-blue-800 pt-4 pb-3 px-5">
          <div v-if="isAuthenticated" class="flex items-center">
            <img
              class="size-8 rounded-full"
              :src="getAvatarUrl(user.profile.image)"
              alt="User avatar" />
            <div class="ml-3">
              <div class="text-base font-medium text-white">
                {{ user.name }}
              </div>
              <div class="text-sm font-medium text-blue-200">
                {{ user.email }}
              </div>
            </div>
          </div>
          <div v-else class="flex flex-col gap-2 mt-2">
            <RouterLink
              to="/login"
              class="w-full text-center text-white bg-blue-800 hover:bg-blue-900 px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
              Вход
            </RouterLink>
            <RouterLink
              to="/register"
              class="w-full text-center text-blue-100 hover:text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
              Регистрация
            </RouterLink>
          </div>
          <div v-if="isAuthenticated" class="mt-3 space-y-1 px-2">
            <DisclosureButton
              v-for="item in userNavigation"
              :key="item.name"
              as="a"
              @click.prevent="item.onclick ? item.onclick() : null"
              href="#"
              class="block rounded-md px-3 py-2 text-base font-medium text-blue-100 hover:bg-blue-800 hover:text-white transition-colors duration-200">
              {{ item.name }}
            </DisclosureButton>
          </div>
        </div>
      </DisclosurePanel>
    </Disclosure>

    <!-- Основное содержимое -->
    <main class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <RouterView />
      </div>
    </main>
  </div>
</template>
