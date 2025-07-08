<script setup>
import { useReportStore } from "../store/report";
import { useAuthStore } from "../store/auth";
import { useRoute } from "vue-router";
import { computed } from "vue";

const route = useRoute();
const authStore = useAuthStore();
const reportStore = useReportStore();

const user = computed(() => authStore.user);
</script>

<template>
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside
      class="w-64 bg-indigo-700 text-white shadow-lg fixed inset-y-0 left-0 z-30">
      <div
        class="p-4 flex items-center justify-between border-b border-indigo-600">
        <h1 class="text-xl font-bold">Админ-панель</h1>
        <RouterLink
          to="/"
          class="p-2 rounded-md hover:bg-indigo-600 transition-colors"
          title="На главную">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
        </RouterLink>
      </div>

      <nav class="p-4">
        <div class="space-y-2">
          <div
            class="rounded-lg hover:bg-indigo-600 transition-colors cursor-pointer">
            <RouterLink
              :to="{ name: 'AdminDashboard' }"
              class="block p-3 rounded-lg transition-colors"
              :class="
                route.name === 'AdminDashboard'
                  ? 'bg-indigo-800 font-semibold'
                  : 'hover:bg-indigo-600'
              ">
              Главная
            </RouterLink>
          </div>
          <div
            class="rounded-lg hover:bg-indigo-600 transition-colors cursor-pointer">
            <RouterLink
              :to="{ name: 'AdminPosts' }"
              class="block p-3 rounded-lg transition-colors"
              :class="
                route.name === 'AdminPosts'
                  ? 'bg-indigo-800 font-semibold'
                  : 'hover:bg-indigo-600'
              ">
              Посты
            </RouterLink>
          </div>
          <div
            class="rounded-lg hover:bg-indigo-600 transition-colors cursor-pointer">
            <RouterLink
              :to="{ name: 'AdminCategories' }"
              class="block p-3 rounded-lg transition-colors"
              :class="
                route.name === 'AdminCategories'
                  ? 'bg-indigo-800 font-semibold'
                  : 'hover:bg-indigo-600'
              ">
              Категории
            </RouterLink>
          </div>
          <div
            class="rounded-lg hover:bg-indigo-600 transition-colors cursor-pointer">
            <RouterLink
              :to="{ name: 'AdminTags' }"
              class="block p-3 rounded-lg transition-colors"
              :class="
                route.name === 'AdminTags'
                  ? 'bg-indigo-800 font-semibold'
                  : 'hover:bg-indigo-600'
              ">
              Теги
            </RouterLink>
          </div>
          <div
            class="rounded-lg hover:bg-indigo-600 transition-colors cursor-pointer">
            <RouterLink
              :to="{ name: 'AdminReports' }"
              class="block p-3 rounded-lg transition-colors"
              :class="
                route.name === 'AdminReports'
                  ? 'bg-indigo-800 font-semibold'
                  : 'hover:bg-indigo-600'
              ">
              <span class="relative inline-block">
                Жалобы
                <span
                  v-if="reportStore.count > 0"
                  class="bg-red-400 px-2 py-1 rounded-full relative bottom-2.5 text-xs">
                  {{ reportStore.count }}
                </span>
              </span>
            </RouterLink>
          </div>
          <div
            class="p-3 rounded-lg hover:bg-indigo-600 transition-colors cursor-pointer">
            Настройки
          </div>
          <div
            class="p-3 rounded-lg hover:bg-indigo-600 transition-colors cursor-pointer">
            Статистика
          </div>
        </div>
      </nav>

      <div class="absolute bottom-0 w-64 p-4 border-t border-indigo-600">
        <div class="flex items-center space-x-3">
          <img
            :src="user?.profile?.image_url || '/img/default-avatar.jpg'"
            alt="User avatar"
            class="size-8 rounded-full" />
          <div>
            <p class="font-medium">{{ user.name }}</p>
            <p class="text-xs text-indigo-200">{{ user.email }}</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 ml-64 overflow-y-auto h-screen p-8 bg-gray-50">
      <div class="bg-white rounded-xl shadow-sm p-6">
        <RouterView />
      </div>
    </main>
  </div>
</template>
