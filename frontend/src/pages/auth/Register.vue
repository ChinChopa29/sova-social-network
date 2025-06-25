<script setup>
import {
  UserIcon,
  LockClosedIcon,
  EnvelopeIcon,
} from "@heroicons/vue/24/outline";
import { RouterLink } from "vue-router";
import { ref } from "vue";
import axiosClient from "../../../../../image upload(vue + laravel api)/frontend/src/axios";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../store/auth";

const router = useRouter();
const authStore = useAuthStore();

const data = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const errors = ref({
  name: [],
  email: [],
  password: [],
});

function register() {
  axiosClient.get("/sanctum/csrf-cookie").then(() => {
    axiosClient
      .post("/api/register", data.value)
      .then(() => {
        authStore.getUser().then(() => {
          router.push({ name: "Home" });
        });
      })
      .catch((error) => {
        if (error.response && error.response.status === 422) {
          const responseErrors = error.response.data.errors || {};
          errors.value = {
            name: responseErrors.name || [],
            email: responseErrors.email || [],
            password: responseErrors.password || [],
          };
        } else {
          errors.value = {
            name: [],
            email: [],
            password: [],
            general: [
              error.response?.data?.message ||
                error.message ||
                "Registration error",
            ],
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
      <div
        class="bg-white rounded-2xl shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl">
        <div
          class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 text-center">
          <h1 class="text-3xl font-bold text-white">Добро пожаловать</h1>
          <p class="text-blue-100 mt-2">Создайте свой аккаунт</p>
        </div>

        <form @submit.prevent="register" class="p-8 space-y-6">
          <!-- Поле имени -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700"
              >Ваш логин</label
            >
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <UserIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                type="text"
                name="name"
                v-model="data.name"
                class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                placeholder="Придумайте логин" />
            </div>
            <p class="text-sm mt-1 text-red-600">
              {{ errors.name ? errors.name[0] : "" }}
            </p>
          </div>

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
                name="email"
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
                name="password"
                v-model="data.password"
                class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                placeholder="••••••••" />
            </div>
            <p class="text-sm mt-1 text-red-600">
              {{ errors.password ? errors.password[0] : "" }}
            </p>
          </div>

          <!-- Подтверждение пароля -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700"
              >Подтвердите пароль</label
            >
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <LockClosedIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                type="password"
                name="password_confirmation"
                v-model="data.password_confirmation"
                class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                placeholder="••••••••" />
            </div>
          </div>

          <!-- Кнопка регистрации -->
          <button
            class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all transform hover:scale-[1.01]">
            Зарегистрироваться
          </button>

          <div class="text-center text-sm text-gray-600">
            Уже есть аккаунт?
            <RouterLink
              to="Login"
              class="font-medium text-indigo-600 hover:text-indigo-500"
              >Войти</RouterLink
            >
          </div>
        </form>
      </div>

      <div class="mt-6 text-center text-xs text-gray-500">
        Нажимая "Зарегистрироваться", вы соглашаетесь с нашими условиями
      </div>
    </div>
  </div>
</template>
