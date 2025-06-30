<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { format } from "date-fns";
import { ru } from "date-fns/locale";
import axiosClient from "../../../axios";

const router = useRoute();
const routerInstance = useRouter();
const tag = ref(null);
const loading = ref(true);

const fetchTag = async () => {
  try {
    const slug = router.params.slug;
    const res = await axiosClient.get(`api/tags/${slug}`);
    tag.value = res.data.data;
  } catch (error) {
    console.error("Ошибка при загрузке тега", error);
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return format(date, "d MMMM yyyy 'года, 'HH:mm", { locale: ru });
};

onMounted(fetchTag);
</script>

<template>
  <div v-if="loading" class="p-4">Загрузка...</div>
  <div v-else class="p-4">
    <button
      @click="routerInstance.go(-1)"
      class="cursor-pointer flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium mb-4">
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
          d="M15 19l-7-7 7-7" />
      </svg>
      Назад
    </button>

    <h1 class="text-xl font-bold mb-2">{{ tag.name }}</h1>
    <p class="text-gray-600">ID: {{ tag.id }}</p>
    <p class="text-gray-600">Автор: {{ tag.user?.name || "—" }}</p>
    <p class="text-gray-600">Создан: {{ formatDate(tag.created_at) }}</p>
    <p class="text-gray-600">Обновлён: {{ formatDate(tag.updated_at) }}</p>
  </div>
</template>
