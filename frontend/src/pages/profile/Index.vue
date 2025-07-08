<script setup>
import { ref, onMounted } from "vue";
import { RouterLink, useRoute } from "vue-router";
import axiosClient from "../../axios";
import { useAuthStore } from "../../store/auth";
import UserPosts from "./Posts.vue";
import CreatePostForm from "../../components/ui/CreatePostForm.vue";
import Toast from "../../components/ui/Toast.vue";

const authStore = useAuthStore();
const profile = ref(null);
const loading = ref(true);
const route = useRoute();
const isOwner = ref(false);
const showDetails = ref(false); // Добавляем состояние для отображения деталей

const backendUrl = import.meta.env.VITE_BACKEND_URL;

const getAvatarUrl = (avatar) => {
  return avatar ? `${backendUrl}${avatar}` : "/img/default-avatar.jpg";
};

const getCoverUrl = (background_image) => {
  return background_image
    ? `${backendUrl}${background_image}`
    : "/img/default-cover.jpg";
};

onMounted(() => {
  const slug = route.params.slug;
  const url = slug ? `/api/profile/${slug}` : `/api/profile`;

  axiosClient
    .get(url)
    .then((response) => {
      profile.value = response.data;
      isOwner.value = authStore.user?.id === profile.value.user_id;
    })
    .catch((error) => {
      console.error("Ошибка при загрузке профиля", error);
    })
    .finally(() => {
      loading.value = false;
    });
});

const showModal = ref(false);
const toastMessage = ref("");
const toastVisible = ref(false);

const handleSuccess = (msg) => {
  toastMessage.value = msg;
  toastVisible.value = true;
  setTimeout(() => (toastVisible.value = false), 3000);
};

function handlePostCreated(message) {
  toastMessage.value = message;
  toastVisible.value = true;
  setTimeout(() => {
    toastVisible.value = false;
  }, 3000);
  showModal.value = false;
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div
        class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="profile" class="max-w-4xl mx-auto">
      <!-- Обложка профиля -->
      <div
        class="h-48 bg-gradient-to-r from-blue-500 to-purple-600 rounded-t-lg overflow-hidden relative">
        <img
          v-if="profile.background_image"
          :src="getCoverUrl(profile.background_image)"
          alt=""
          class="w-full h-full object-cover" />

        <!-- Блок с аватаркой и именем слева -->
        <div
          class="absolute bottom-0 left-0 flex items-end p-4 w-full bg-gradient-to-t from-black/50 to-transparent">
          <div class="flex items-end gap-4">
            <!-- Аватар -->
            <img
              :src="getAvatarUrl(profile.avatar)"
              alt="Avatar"
              class="w-24 h-24 rounded-full border-4 border-white bg-white object-cover shadow-lg" />

            <!-- Имя и кнопки -->
            <div class="mb-2">
              <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-white">
                  {{ profile.user.name }}
                </h1>
                <span
                  v-if="profile.is_verified"
                  class="bg-blue-500 text-white p-1 rounded-full">
                  <!-- иконка -->
                </span>
              </div>

              <!-- Кнопки -->
              <div class="flex gap-2 mt-2">
                <button
                  v-if="isOwner"
                  @click="showModal = true"
                  class="inline-flex items-center px-3 py-1 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                  Создать пост
                </button>

                <RouterLink
                  v-if="isOwner"
                  :to="{ name: 'ProfileEdit' }"
                  class="inline-flex items-center px-3 py-1 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm">
                  Редактировать
                </RouterLink>

                <button
                  @click="showDetails = !showDetails"
                  class="inline-flex items-center px-3 py-1 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm">
                  {{ showDetails ? "Скрыть детали" : "Показать детали" }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Основной контент -->
      <div class="bg-white rounded-b-lg shadow-md p-6 mb-6">
        <!-- Скрываемая информация -->
        <div v-if="showDetails" class="mb-8">
          <!-- О себе -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              О себе
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Левая колонка -->
              <div>
                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">Имя</h3>
                  <p class="text-gray-800">
                    {{ profile.name || "Не указано" }}
                  </p>
                </div>

                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">Пол</h3>
                  <p class="text-gray-800" v-if="profile.gender === 'male'">
                    Мужской
                  </p>
                  <p
                    v-else-if="profile.gender === 'female'"
                    class="text-gray-800">
                    Женский
                  </p>
                  <p v-else class="text-gray-800">
                    {{ profile.gender || "Не указано" }}
                  </p>
                </div>

                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">
                    Дата рождения
                  </h3>
                  <p class="text-gray-800">
                    {{
                      profile.birthday
                        ? new Date(profile.birthday).toLocaleDateString()
                        : "Не указано"
                    }}
                  </p>
                </div>
              </div>

              <!-- Правая колонка -->
              <div>
                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">Биография</h3>
                  <p class="text-gray-700 whitespace-pre-line">
                    {{
                      profile.bio ||
                      "Пользователь пока не добавил информацию о себе"
                    }}
                  </p>
                </div>

                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">Язык</h3>
                  <p class="text-gray-800">
                    {{
                      profile.language === "en"
                        ? "English"
                        : profile.language === "ru"
                        ? "Русский"
                        : "Не указано"
                    }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Контактная информация -->
          <div>
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Контактная информация
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Левая колонка -->
              <div>
                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">Телефон</h3>
                  <p class="text-gray-800">
                    {{ profile.phone || "Не указано" }}
                  </p>
                </div>
              </div>

              <!-- Правая колонка -->
              <div>
                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">Вебсайт</h3>
                  <a
                    v-if="profile.website"
                    :href="profile.website"
                    target="_blank"
                    class="text-blue-500 hover:underline">
                    {{ profile.website }}
                  </a>
                  <p v-else class="text-gray-800">Не указано</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Адрес -->
          <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Адрес
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">Страна</h3>
                  <p class="text-gray-800">
                    {{ profile.country || "Не указано" }}
                  </p>
                </div>
              </div>

              <div>
                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">Город</h3>
                  <p class="text-gray-800">
                    {{ profile.city || "Не указано" }}
                  </p>
                </div>

                <div class="mb-4">
                  <h3 class="text-sm font-medium text-gray-500">Адрес</h3>
                  <p class="text-gray-800">
                    {{ profile.address || "Не указано" }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Статус публичности -->
        <div
          v-if="!profile.is_public && isOwner"
          class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg
                class="h-5 w-5 text-yellow-400"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-yellow-700">
                Ваш профиль скрыт от других пользователей. Вы можете изменить
                это в
                <RouterLink
                  :to="{ name: 'ProfileEdit' }"
                  class="font-medium underline text-yellow-700 hover:text-yellow-600">
                  настройках профиля </RouterLink
                >.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Посты -->
      <div class="max-w-4xl mx-auto">
        <CreatePostForm
          :show="showModal"
          @close="showModal = false"
          @success="handleSuccess" />

        <UserPosts :user-id="profile.user_id" />
      </div>
    </div>

    <!-- Профиль не найден -->
    <div v-else class="flex flex-col items-center justify-center h-64">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-16 w-16 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="mt-4 text-lg text-gray-600">Профиль не найден</p>
    </div>

    <Toast :message="toastMessage" :show="toastVisible" />
  </div>
</template>
