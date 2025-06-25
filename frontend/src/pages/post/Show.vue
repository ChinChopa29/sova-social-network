<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axiosClient from "../../axios";
import { formatDistanceToNow } from "date-fns";
import { ru } from "date-fns/locale";

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

const openImage = (img) => {
  previewImage.value = imageUrl(img);
};

const formattedUpdatedAt = () => {
  if (!post.value?.updated_at) return "";
  return formatDistanceToNow(new Date(post.value.updated_at), {
    addSuffix: true,
    locale: ru,
  });
};

const formattedCreatedAt = () => {
  if (!post.value?.created_at) return "";
  return formatDistanceToNow(new Date(post.value.created_at), {
    addSuffix: true,
    locale: ru,
  });
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
  <div v-if="loading" class="text-center py-12">
    <div class="animate-pulse flex flex-col space-y-4">
      <div class="h-8 bg-gray-200 rounded w-3/4 mx-auto"></div>
      <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto"></div>
      <div class="h-64 bg-gray-200 rounded"></div>
      <div class="h-4 bg-gray-200 rounded"></div>
      <div class="h-4 bg-gray-200 rounded w-5/6"></div>
    </div>
  </div>

  <div v-else-if="post" class="max-w-4xl mx-auto px-4 py-8">
    <!-- Шапка поста -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ post.title }}</h1>

      <div
        class="flex flex-wrap items-center justify-between text-sm text-gray-500 mb-4">
        <div class="flex items-center space-x-3">
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
          <span>·</span>
          <span>
            {{
              formatDistanceToNow(new Date(post.created_at), {
                addSuffix: true,
                locale: ru,
              })
            }}
          </span>
          <span>{{ formattedCreatedAt() }}</span>
          <span>·</span>
          <span>👁 {{ post.view_count }}</span>
        </div>

        <div class="flex items-center space-x-4">
          <span
            class="px-2 py-1 rounded-full text-xs font-medium"
            :class="{
              'bg-gray-100 text-gray-800': post.status === 'draft',
              'bg-green-100 text-green-800': post.status === 'published',
              'bg-blue-100 text-blue-800': post.status === 'archived',
              'bg-purple-100 text-purple-800': post.status === 'private',
            }">
            {{ post.status }}
          </span>
          <span class="text-blue-600 hover:underline">
            {{ post.category?.name || "Без категории" }}
          </span>
        </div>
      </div>

      <!-- Теги -->
      <div v-if="post.tags?.length" class="flex flex-wrap gap-2 mb-6">
        <span
          v-for="tag in post.tags"
          :key="tag.id"
          class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full hover:bg-gray-200 transition">
          #{{ tag.name }}
        </span>
      </div>
    </div>

    <!-- Основное содержимое -->
    <article class="prose max-w-none mb-12">
      <div v-html="post.content"></div>
    </article>

    <!-- Галерея изображений -->
    <div v-if="post.images?.length" class="mb-12">
      <h2 class="text-xl font-semibold mb-4">Галерея</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="(img, index) in post.images"
          :key="index"
          class="relative group overflow-hidden rounded-lg shadow-md hover:shadow-lg transition-shadow">
          <img
            :src="imageUrl(img)"
            @click="openImage(img)"
            class="w-full h-64 object-cover cursor-zoom-in hover:opacity-90 transition-opacity" />
          <div
            class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
            <span class="text-white text-sm bg-black/50 px-2 py-1 rounded"
              >Нажмите для просмотра</span
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Блок с реакциями -->
    <div
      class="flex items-center justify-center space-x-8 py-6 border-y border-gray-200 mb-12">
      <button class="flex flex-col items-center group">
        <span class="text-2xl group-hover:text-green-600 transition">👍</span>
        <span class="text-sm text-gray-500">{{ post.like_count }}</span>
      </button>
      <button class="flex flex-col items-center group">
        <span class="text-2xl group-hover:text-red-600 transition">👎</span>
        <span class="text-sm text-gray-500">{{ post.dislike_count }}</span>
      </button>
      <button class="flex flex-col items-center group">
        <span class="text-2xl group-hover:text-blue-600 transition">💬</span>
        <span class="text-sm text-gray-500">Комментировать</span>
      </button>
    </div>

    <!-- Блок комментариев -->
    <section id="comments" class="mb-12">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold">Комментарии</h2>
        <span class="text-sm text-gray-500"
          >{{ post.comments_count || 0 }} комментариев</span
        >
      </div>

      <div v-if="post.is_commentable">
        <!-- Форма добавления комментария -->
        <div class="bg-gray-50 rounded-lg p-4 mb-8">
          <textarea
            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
            rows="3"
            placeholder="Напишите ваш комментарий..."></textarea>
          <div class="flex justify-end mt-2">
            <button
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
              Отправить
            </button>
          </div>
        </div>

        <!-- Список комментариев (шаблон) -->
        <div class="space-y-6">
          <div
            v-for="i in 3"
            :key="i"
            class="border-b border-gray-200 pb-6 last:border-0">
            <div class="flex items-start space-x-3 mb-3">
              <img
                src="/img/default-avatar.jpg"
                alt="avatar"
                class="w-10 h-10 rounded-full object-cover" />
              <div>
                <div class="font-medium">Имя пользователя</div>
                <div class="text-sm text-gray-500">3 дня назад</div>
              </div>
            </div>
            <div class="pl-13">
              <p class="text-gray-700 mb-3">
                Это пример комментария. Здесь будет текст, написанный
                пользователем.
              </p>
              <button class="text-sm text-blue-600 hover:underline">
                Ответить
              </button>
            </div>

            <!-- Вложенные комментарии -->
            <div class="mt-4 pl-6 border-l-2 border-gray-200 space-y-4">
              <div v-for="j in 2" :key="j" class="pt-4">
                <div class="flex items-start space-x-3 mb-3">
                  <img
                    src="/img/default-avatar.jpg"
                    alt="avatar"
                    class="w-8 h-8 rounded-full object-cover" />
                  <div>
                    <div class="font-medium">Другой пользователь</div>
                    <div class="text-sm text-gray-500">1 день назад</div>
                  </div>
                </div>
                <div class="pl-11">
                  <p class="text-gray-700">Это ответ на комментарий выше.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="bg-gray-50 rounded-lg p-8 text-center">
        <p class="text-gray-500">Комментарии к этому посту закрыты</p>
      </div>
    </section>
  </div>

  <div v-else class="text-center py-12">
    <p class="text-gray-500 text-lg">Пост не найден</p>
  </div>

  <!-- Превью изображения -->
  <div
    v-if="previewImage"
    @click="previewImage = null"
    class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 cursor-zoom-out">
    <div class="max-w-6xl max-h-screen">
      <img
        :src="previewImage"
        class="max-w-full max-h-screen object-contain rounded" />
    </div>
  </div>
</template>

<style scoped>
.prose >>> img {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 1rem auto;
}

.prose >>> pre {
  background-color: #f8f8f8;
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
  margin: 1rem 0;
}

.prose >>> code {
  background-color: #f8f8f8;
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  font-family: monospace;
}

.prose >>> blockquote {
  border-left: 4px solid #ddd;
  padding-left: 1rem;
  color: #666;
  font-style: italic;
  margin: 1rem 0;
}

.prose >>> table {
  width: 100%;
  border-collapse: collapse;
  margin: 1rem 0;
}

.prose >>> th,
.prose >>> td {
  border: 1px solid #ddd;
  padding: 0.5rem;
  text-align: left;
}

.prose >>> th {
  background-color: #f5f5f5;
}
</style>
