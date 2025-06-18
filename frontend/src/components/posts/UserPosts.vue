<template>
  <div>
    <h2 class="text-lg font-semibold mb-4">Посты пользователя</h2>

    <div v-if="loading" class="text-gray-500">Загрузка...</div>

    <div v-else-if="posts.length === 0" class="text-gray-500">Постов нет.</div>

    <div v-else class="space-y-4">
      <div
        v-for="post in posts"
        :key="post.id"
        class="bg-white p-4 rounded shadow border">
        <h3 class="font-semibold text-lg">{{ post.title }}</h3>
        <p class="text-sm text-gray-600">{{ post.created_at }}</p>
        <p class="mt-2 text-gray-700">{{ post.body }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axiosClient from "../../axios";

const props = defineProps({
  userId: Number,
});

const posts = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const { data } = await axiosClient.get(`/api/users/${props.userId}/posts`);
    posts.value = data;
  } finally {
    loading.value = false;
  }
});
</script>
