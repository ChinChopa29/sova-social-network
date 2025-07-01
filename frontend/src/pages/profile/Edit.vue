<script setup>
import { ref, onMounted, watch } from "vue";
import axiosClient from "../../axios";
import { useRouter } from "vue-router";
import { Disclosure } from "@headlessui/vue";

const router = useRouter();
const activeTab = ref("main");

const backendUrl = import.meta.env.VITE_BACKEND_URL;

const getAvatarUrl = (avatar) => {
  return avatar ? `${backendUrl}${avatar}` : "/img/default-avatar.jpg";
};

const getCoverUrl = (background_image) => {
  return background_image
    ? `${backendUrl}${background_image}`
    : "/img/default-cover.jpg";
};

watch(activeTab, (val) => {
  console.log("activeTab is:", val);
});

const avatarFile = ref(null);
const coverFile = ref(null);

const profile = ref({
  name: "",
  gender: "",
  phone: "",
  address: "",
  city: "",
  country: "",
  bio: "",
  website: "",
  is_public: true,
  is_verified: false,
  language: "ru",
  avatar: null,
  background_image: null,
  slug: "",
});

const tabs = [
  { id: "main", name: "Основное" },
  { id: "contacts", name: "Контактные данные" },
  { id: "address", name: "Адрес" },
  { id: "settings", name: "Настройки" },
];

onMounted(() => {
  axiosClient.get("/api/profile").then((response) => {
    Object.assign(profile.value, response.data);
    if (profile.value.birthday) {
      profile.value.birthday = profile.value.birthday.substring(0, 10);
    }
  });
});

function handleAvatarUpload(event) {
  const file = event.target.files[0];
  avatarFile.value = file;

  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      profile.value.avatar = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}

function handleCoverUpload(event) {
  const file = event.target.files[0];
  coverFile.value = file;

  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      profile.value.background_image = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}

function updateProfile() {
  const formData = new FormData();

  if (profile.value.name !== null) {
    formData.append("name", profile.value.name || "");
  }

  if (profile.value.gender !== null) {
    formData.append("gender", profile.value.gender || "");
  }

  if (profile.value.birthday !== null) {
    formData.append("birthday", profile.value.birthday || "");
  }

  if (profile.value.phone !== null) {
    formData.append("phone", profile.value.phone || "");
  }

  if (profile.value.address !== null) {
    formData.append("address", profile.value.address || "");
  }

  if (profile.value.city !== null) {
    formData.append("city", profile.value.city || "");
  }

  if (profile.value.country !== null) {
    formData.append("country", profile.value.country || "");
  }

  if (profile.value.bio !== null) {
    formData.append("bio", profile.value.bio || "");
  }

  if (profile.value.website !== null) {
    formData.append("website", profile.value.website || "");
  }

  if (profile.value.language !== null) {
    formData.append("language", profile.value.language);
  }

  formData.append("is_public", profile.value.is_public ? "1" : "0");
  formData.append("is_verified", profile.value.is_verified ? "1" : "0");

  if (avatarFile.value) {
    formData.append("avatar", avatarFile.value);
  }

  if (coverFile.value) {
    formData.append("background_image", coverFile.value);
  }

  axiosClient
    .post(`/api/profile/${profile.value.slug}?_method=PUT`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(() => {
      router.push({ name: "Profile" });
    })
    .catch((error) => {
      if (error.response?.status === 422) {
        console.log("Ошибки валидации:", error.response.data.errors);
      }
    });
}
</script>

<template>
  <div class="min-h-full">
    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div
          class="px-6 py-5 border-b border-gray-200 flex items-start space-x-4">
          <!-- Кнопка Назад -->
          <button
            @click="$router.back()"
            class="mt-1 text-sm text-blue-600 hover:underline transition whitespace-nowrap">
            ← Назад
          </button>

          <!-- Заголовок и описание -->
          <div>
            <h2 class="text-xl font-semibold text-gray-800">
              Редактирование профиля
            </h2>
            <p class="text-sm text-gray-500">
              Обновите ваши личные данные и настройки
            </p>
          </div>
        </div>

        <div class="border-b border-gray-200">
          <nav class="flex -mb-px">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              ]">
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <form @submit.prevent="updateProfile" class="divide-y divide-gray-200">
          <div v-if="activeTab === 'main'" class="px-6 py-5 space-y-6">
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
              <div class="sm:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Аватар</label
                >
                <div class="flex items-center">
                  <img
                    :src="getAvatarUrl(profile.avatar)"
                    class="h-16 w-16 rounded-full object-cover" />
                  <div class="ml-4">
                    <input
                      type="file"
                      @change="handleAvatarUpload"
                      accept="image/*"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    <p class="mt-1 text-xs text-gray-500">
                      JPG, GIF или PNG. Макс. 2MB
                    </p>
                  </div>
                </div>
              </div>

              <div class="sm:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Обложка профиля</label
                >
                <div
                  class="relative group h-32 w-full rounded-md overflow-hidden bg-gray-100">
                  <img
                    v-if="profile.background_image"
                    :src="getCoverUrl(profile.background_image)"
                    class="h-full w-full object-cover" />
                  <div
                    v-else
                    class="flex items-center justify-center h-full w-full text-gray-500 text-sm">
                    Нет изображения
                  </div>

                  <input
                    type="file"
                    @change="handleCoverUpload"
                    accept="image/*"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />

                  <div
                    class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                    <span class="text-white text-sm font-medium"
                      >Нажмите для загрузки</span
                    >
                  </div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
              <div class="sm:col-span-3">
                <label
                  for="name"
                  class="block text-sm font-medium text-gray-700"
                  >Имя</label
                >
                <input
                  v-model="profile.name"
                  type="text"
                  id="name"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" />
              </div>

              <div class="sm:col-span-3">
                <label
                  for="gender"
                  class="block text-sm font-medium text-gray-700"
                  >Пол</label
                >
                <select
                  v-model="profile.gender"
                  id="gender"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                  <option value="">Не указан</option>
                  <option value="male">Мужской</option>
                  <option value="female">Женский</option>
                  <option value="other">Другой</option>
                </select>
              </div>

              <div class="sm:col-span-3">
                <label
                  for="birthday"
                  class="block text-sm font-medium text-gray-700">
                  Дата рождения
                </label>
                <input
                  v-model="profile.birthday"
                  type="date"
                  id="birthday"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" />
              </div>

              <div class="sm:col-span-6">
                <label for="bio" class="block text-sm font-medium text-gray-700"
                  >О себе</label
                >
                <textarea
                  v-model="profile.bio"
                  id="bio"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"></textarea>
              </div>
            </div>
          </div>

          <div v-if="activeTab === 'contacts'" class="px-6 py-5 space-y-6">
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
              <!-- Телефон -->
              <div class="sm:col-span-3">
                <label
                  for="phone"
                  class="block text-sm font-medium text-gray-700"
                  >Телефон</label
                >
                <input
                  v-model="profile.phone"
                  type="tel"
                  id="phone"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" />
              </div>

              <div class="sm:col-span-3">
                <label
                  for="website"
                  class="block text-sm font-medium text-gray-700"
                  >Вебсайт</label
                >
                <input
                  v-model="profile.website"
                  type="url"
                  id="website"
                  placeholder="https://example.com"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" />
              </div>
            </div>
          </div>

          <div v-if="activeTab === 'address'" class="px-6 py-5 space-y-6">
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
              <!-- Страна -->
              <div class="sm:col-span-3">
                <label
                  for="country"
                  class="block text-sm font-medium text-gray-700"
                  >Страна</label
                >
                <input
                  v-model="profile.country"
                  type="text"
                  id="country"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" />
              </div>

              <div class="sm:col-span-3">
                <label
                  for="city"
                  class="block text-sm font-medium text-gray-700"
                  >Город</label
                >
                <input
                  v-model="profile.city"
                  type="text"
                  id="city"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" />
              </div>

              <div class="sm:col-span-6">
                <label
                  for="address"
                  class="block text-sm font-medium text-gray-700"
                  >Адрес</label
                >
                <input
                  v-model="profile.address"
                  type="text"
                  id="address"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" />
              </div>
            </div>
          </div>

          <div v-if="activeTab === 'settings'" class="px-6 py-5 space-y-6">
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
              <div class="sm:col-span-3">
                <label
                  for="language"
                  class="block text-sm font-medium text-gray-700"
                  >Язык</label
                >
                <select
                  v-model="profile.language"
                  id="language"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                  <option value="en">English</option>
                  <option value="ru">Русский</option>
                </select>
              </div>

              <div class="sm:col-span-3">
                <label
                  for="is_public"
                  class="block text-sm font-medium text-gray-700"
                  >Видимость профиля</label
                >
                <select
                  v-model="profile.is_public"
                  id="is_public"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                  <option :value="true">Публичный</option>
                  <option :value="false">Приватный</option>
                </select>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 bg-gray-50 text-right">
            <button
              type="button"
              @click="router.push({ name: 'Profile' })"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Отмена
            </button>
            <button
              type="submit"
              class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Сохранить изменения
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>
