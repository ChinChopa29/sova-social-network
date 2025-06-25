<script setup>
import { ref, onMounted, computed } from "vue";
import axiosClient from "../../axios";
import { formatDistanceToNow } from "date-fns";
import { ru } from "date-fns/locale";

const props = defineProps({
  userId: Number,
});

const imageUrl = (img) => {
  const base = import.meta.env.VITE_BACKEND_URL;
  const path = typeof img === "string" ? img : img?.path || "";

  return path.startsWith("/storage")
    ? `${base}${path}`
    : `${base}/storage/${path}`;
};

const previewImage = ref(null);

const openImage = (img) => {
  previewImage.value = imageUrl(img);
};

const posts = ref([]);
const loading = ref(true);

const safePosts = computed(() => posts.value.filter((p) => p !== null));

onMounted(async () => {
  try {
    const { data } = await axiosClient.get(`/api/users/${props.userId}/posts`);
    posts.value = data.data;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div v-if="loading" class="text-center py-8">
    <div class="animate-pulse flex flex-col space-y-4">
      <div class="h-32 bg-gray-200 rounded"></div>
      <div class="h-32 bg-gray-200 rounded"></div>
      <div class="h-32 bg-gray-200 rounded"></div>
    </div>
  </div>

  <div v-else class="space-y-6">
    <div
      v-for="post in safePosts"
      :key="post.id"
      class="bg-white rounded-xl shadow border border-gray-100 hover:shadow-md transition p-6 space-y-4">
      <!-- Автор и дата -->
      <div class="flex justify-between items-center text-sm text-gray-500">
        <div v-if="post.user" class="flex items-center space-x-2">
          <img
            :src="
              post.user?.profile?.avatar
                ? imageUrl(post.user.profile.avatar)
                : '/img/default-avatar.jpg'
            "
            alt="avatar"
            class="w-8 h-8 rounded-full object-cover" />
          <span>{{ post.user.name }}</span>
        </div>
        <div v-else>
          <span>Неизвестен</span>
        </div>
        <span>{{
          formatDistanceToNow(new Date(post.created_at), {
            addSuffix: true,
            locale: ru,
          })
        }}</span>
      </div>

      <!-- Заголовок -->
      <router-link
        :to="{ name: 'posts.show', params: { slug: post.slug } }"
        class="text-xl font-semibold text-gray-900 hover:text-blue-600 transition">
        {{ post.title }}
      </router-link>

      <!-- Контент -->
      <div class="prose max-w-none text-gray-700" v-html="post.content"></div>

      <!-- Галерея -->
      <div
        v-if="post.images?.length"
        class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-4 h-72 overflow-hidden">
        <!-- Левая большая -->
        <div class="md:col-span-2 h-full">
          <img
            :src="imageUrl(post.images[0])"
            @click="openImage(post.images[0])"
            class="w-full h-full object-cover rounded-lg cursor-pointer hover:opacity-80 transition" />
        </div>

        <!-- Правые миниатюры -->
        <div class="grid grid-cols-2 gap-2 h-full">
          <img
            v-for="(img, i) in post.images.slice(1, 5)"
            :key="i"
            :src="imageUrl(img)"
            @click="openImage(img)"
            class="w-full h-full object-cover rounded-lg cursor-pointer hover:opacity-80 transition" />

          <!-- Если картинок больше 5 — показать «+N» -->
          <div
            v-if="post.images.length > 5"
            class="w-full h-1/2 bg-gray-200 rounded-lg flex items-center justify-center text-gray-700 text-sm font-semibold cursor-pointer hover:bg-gray-300 transition"
            @click="openImage(post.images[5])">
            +{{ post.images.length - 5 }}
          </div>
        </div>
      </div>

      <!-- Категория, теги -->
      <div class="text-sm text-gray-500 flex flex-wrap gap-4 mt-2">
        <span>Категория: {{ post.category?.name || "Без категории" }}</span>
        <span v-if="post.tags?.length">
          Теги:
          <span
            v-for="tag in post.tags"
            :key="tag.id"
            class="ml-1 text-blue-600"
            >#{{ tag.name }}</span
          >
        </span>
      </div>

      <!-- Статистика и комментарии -->
      <div
        class="flex justify-between items-center pt-3 border-t border-gray-100 text-sm text-gray-600">
        <div class="flex space-x-4">
          <span>👁 {{ post.view_count }}</span>
          <span>👍 {{ post.like_count }}</span>
          <span>👎 {{ post.dislike_count }}</span>
        </div>
        <router-link
          v-if="post.is_commentable"
          :to="{
            name: 'posts.show',
            params: { slug: post.slug },
            hash: '#comments',
          }"
          class="text-blue-600 hover:underline">
          Комментировать
        </router-link>
        <span v-else>Комментарии закрыты</span>
      </div>
    </div>

    <div v-if="safePosts.length === 0" class="text-center py-12 text-gray-500">
      Посты не найдены
    </div>
  </div>

  <div
    v-if="previewImage"
    @click="previewImage = null"
    class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center cursor-zoom-out">
    <img
      :src="previewImage"
      class="max-w-full max-h-full rounded-lg shadow-lg" />
  </div>
</template>

<style scoped>
.prose >>> img {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
}

.prose >>> pre {
  background-color: #f8f8f8;
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
}

.prose >>> code {
  background-color: #f8f8f8;
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  font-family: monospace;
}
</style>
