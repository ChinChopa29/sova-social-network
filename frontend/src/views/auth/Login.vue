<script setup>
import { LockClosedIcon, EnvelopeIcon } from "@heroicons/vue/24/outline";
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../store/auth";
import axiosClient from "../../axios";

const router = useRouter();
const authStore = useAuthStore();

const data = ref({
  email: "",
  password: "",
  remember: false,
});

const errors = ref({
  email: [],
  password: [],
});

function login() {
  axiosClient.get("/sanctum/csrf-cookie").then(() => {
    axiosClient
      .post("/api/login", data.value)
      .then(() => {
        authStore.getUser().then(() => {
          router.push({ name: "Home" });
        });
      })
      .catch((error) => {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          errors.value = {
            email: [],
            password: [],
            general: [error.response?.data?.message || "Ошибка авторизации"],
          };
        }
      });
  });
}
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Карточка формы -->
      <div
        class="bg-white rounded-2xl shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl">
        <!-- Декоративная верхняя часть -->
        <div
          class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 text-center">
          <h1 class="text-3xl font-bold text-white">С возвращением</h1>
          <p class="text-blue-100 mt-2">Войдите в свой аккаунт</p>
        </div>

        <!-- Тело формы -->
        <form @submit.prevent="login" class="p-8 space-y-6">
          <!-- Поле email -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <EnvelopeIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                type="email"
                v-model="data.email"
                class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                placeholder="example@mail.com" />
            </div>
            <p class="text-sm mt-1 text-red-600">
              {{ errors.email ? errors.email[0] : "" }}
            </p>
          </div>

          <!-- Поле пароля -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700"
              >Пароль</label
            >
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <LockClosedIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                type="password"
                v-model="data.password"
                class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                placeholder="••••••••" />
            </div>
            <p class="text-sm mt-1 text-red-600">
              {{ errors.password ? errors.password[0] : "" }}
            </p>
          </div>

          <!-- Запомнить меня + забыли пароль -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember-me"
                name="remember-me"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
              <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                Запомнить меня
              </label>
            </div>
            <div class="text-sm">
              <RouterLink
                to="/forgot-password"
                class="font-medium text-indigo-600 hover:text-indigo-500">
                Забыли пароль?
              </RouterLink>
            </div>
          </div>

          <!-- Кнопка входа -->
          <button
            type="submit"
            class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all transform hover:scale-[1.01]">
            Войти
          </button>

          <!-- Ссылка на регистрацию -->
          <div class="text-center text-sm text-gray-600">
            Нет аккаунта?
            <RouterLink
              to="/register"
              class="font-medium text-indigo-600 hover:text-indigo-500">
              Зарегистрироваться
            </RouterLink>
          </div>
        </form>
      </div>

      <!-- Декоративные элементы -->
      <div class="mt-6 text-center text-xs text-gray-500">
        © 2025 Sova. Все права защищены.
      </div>
    </div>
  </div>
</template>
