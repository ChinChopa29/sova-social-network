<script setup>
import { ref, onMounted } from "vue";
import { RouterView, useRoute } from "vue-router";
import axiosClient from "../../axios";
import { formatDistanceToNow } from "date-fns";
import { ru } from "date-fns/locale";
import Actions from "./Actions.vue";
import Comments from "../post/comment/Index.vue";

const route = useRoute();
const post = ref(null);
const loading = ref(true);
const previewImage = ref(null);

const imageUrl = (img) => {
  const base = import.meta.env.VITE_BACKEND_URL;
  const path = typeof img === "string" ? img : img?.path || "";
  return path.startsWith("/storage")
    ? `${base}${path}`
    : `${base}/storage/${path}`;
};

const statusMap = {
  draft: "Черновик",
  published: "Опубликован",
  archived: "В архиве",
  private: "Приватный",
};

const openImage = (img) => {
  console.log("Открываем изображение", img);
  previewImage.value = imageUrl(img);
};

onMounted(async () => {
  try {
    const slug = route.params.slug;
    const { data } = await axiosClient.get(`/api/posts/${slug}`);
    post.value = data;
  } catch (err) {
    console.error("Ошибка загрузки поста", err);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div v-if="loading" class="max-w-4xl mx-auto px-4 py-8">
    <div class="animate-pulse space-y-8">
      <div class="h-10 bg-gray-200 rounded w-3/4"></div>
      <div class="flex items-center space-x-4">
        <div class="h-8 w-8 bg-gray-200 rounded-full"></div>
        <div class="h-4 bg-gray-200 rounded w-32"></div>
        <div class="h-4 bg-gray-200 rounded w-24"></div>
      </div>
      <div class="h-64 bg-gray-200 rounded-lg"></div>
      <div class="space-y-2">
        <div class="h-4 bg-gray-200 rounded"></div>
        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
      </div>
    </div>
  </div>

  <div v-else-if="post" class="max-w-4xl mx-auto px-4 py-8">
    <!-- Шапка поста -->
    <div class="mb-12">
      <!-- Назад и заголовок -->
      <div class="">
        <button
          @click="$router.back()"
          class="text-sm text-blue-600 hover:underline transition whitespace-nowrap mb-1">
          ← Назад
        </button>

        <div class="flex gap-2 items-center py-4">
          <img
            :src="
              post.user?.profile?.avatar
                ? imageUrl(post.user.profile.avatar)
                : '/img/default-avatar.jpg'
            "
            class="w-16 h-16 rounded-full object-cover border-2 border-white shadow"
            alt="avatar" />
          <RouterLink
            v-if="post.user?.profile?.slug"
            :to="`/profile/${post.user.profile.slug}`"
            class="font-semibold text-xl text-blue-600 hover:underline">
            {{ post.user.name }}
          </RouterLink>
        </div>
      </div>

      <!-- Категория и статус -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-3xl font-bold text-gray-900 leading-tight">
          {{ post.title }}
        </h1>
        <div class="flex items-center gap-2">
          <router-link
            v-if="post.category"
            :to="{
              name: 'categories.show',
              params: { slug: post.category.slug },
            }"
            class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-sm font-medium hover:bg-blue-100 transition">
            {{ post.category.name }}
          </router-link>

          <span
            class="px-3 py-1 rounded-full text-sm font-medium capitalize"
            :class="{
              'bg-gray-100 text-gray-800': post.status === 'draft',
              'bg-green-100 text-green-800': post.status === 'published',
              'bg-blue-100 text-blue-800': post.status === 'archived',
              'bg-purple-100 text-purple-800': post.status === 'private',
            }">
            {{ statusMap[post.status] || "Неизвестно" }}
          </span>
        </div>
      </div>
    </div>

    <!-- Основное содержимое -->
    <article class="prose prose-lg max-w-none mb-16">
      <div v-html="post.content"></div>
    </article>

    <!-- Галерея изображений -->
    <div v-if="post.images?.length" class="mb-16">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Галерея</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="(img, index) in post.images"
          :key="index"
          class="relative group overflow-hidden rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
          <img
            :src="imageUrl(img)"
            @click="openImage(img)"
            class="w-full h-64 object-cover cursor-zoom-in hover:opacity-90 transition-opacity"
            :alt="`Изображение ${index + 1} из поста ${post.title}`" />
          <div
            class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4 pointer-events-none">
            <span class="text-white text-sm">Нажмите для просмотра</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Блок с реакциями -->
    <Actions
      :post-id="post.id"
      :initial-likes="post.like_count"
      :initial-dislikes="post.dislike_count"
      class="mb-16" />

    <!-- Комментарии -->
    <Comments v-if="post.is_commentable" :slug="post.slug" :post-id="post.id" />
    <div
      v-else
      class="bg-gray-50 rounded-xl p-6 text-center text-gray-500 mb-16">
      Комментарии к этому посту закрыты
    </div>
  </div>

  <div v-else class="max-w-4xl mx-auto px-4 py-16 text-center">
    <div class="bg-gray-50 rounded-xl p-12">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-16 w-16 mx-auto text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="text-xl font-medium text-gray-900 mt-4">Пост не найден</h3>
      <p class="mt-2 text-gray-500">Возможно, он был удален или перемещен.</p>
      <router-link
        to="/"
        class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        На главную
      </router-link>
    </div>
  </div>

  <!-- Превью изображения -->
  <div
    v-if="previewImage"
    @click="previewImage = null"
    class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 cursor-zoom-out transition-opacity duration-300">
    <div class="max-w-6xl w-full">
      <img
        :src="previewImage"
        class="w-full h-auto max-h-[90vh] object-contain rounded-lg shadow-2xl transition-transform duration-300 scale-100 hover:scale-105" />
    </div>
  </div>
</template>

<style scoped>
.prose >>> img {
  max-width: 100%;
  height: auto;
  border-radius: 0.75rem;
  margin: 1.5rem auto;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.prose >>> pre {
  background-color: #1e293b;
  color: #f8fafc;
  padding: 1.25rem;
  border-radius: 0.75rem;
  overflow-x: auto;
  margin: 1.5rem 0;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.prose >>> code {
  background-color: #1e293b;
  color: #f8fafc;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas,
    "Liberation Mono", "Courier New", monospace;
  font-size: 0.875rem;
}

.prose >>> blockquote {
  border-left: 4px solid #3b82f6;
  padding-left: 1.5rem;
  color: #64748b;
  font-style: italic;
  margin: 1.5rem 0;
}

.prose >>> table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5rem 0;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

.prose >>> th,
.prose >>> td {
  border: 1px solid #e2e8f0;
  padding: 0.75rem;
  text-align: left;
}

.prose >>> th {
  background-color: #f8fafc;
  font-weight: 600;
}

.prose >>> a {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.prose >>> a:hover {
  text-decoration: underline;
}

.prose >>> h1,
.prose >>> h2,
.prose >>> h3,
.prose >>> h4,
.prose >>> h5,
.prose >>> h6 {
  color: #1e293b;
  font-weight: 700;
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.prose >>> h1 {
  font-size: 2rem;
  line-height: 2.5rem;
}

.prose >>> h2 {
  font-size: 1.5rem;
  line-height: 2rem;
}

.prose >>> ul,
.prose >>> ol {
  padding-left: 1.5rem;
  margin: 1rem 0;
}

.prose >>> li {
  margin: 0.5rem 0;
}
</style>
