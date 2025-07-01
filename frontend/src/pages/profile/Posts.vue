<script setup>
import { ref, onMounted, computed, nextTick } from "vue";
import axiosClient from "../../axios";
import { formatDistanceToNow } from "date-fns";
import { ru } from "date-fns/locale";
import Actions from "../post/Actions.vue";

const props = defineProps({
  userId: Number,
});

const currentUserId = ref(null);

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

const previewImage = ref(null);
const openImage = (img) => {
  previewImage.value = imageUrl(img);
};

const posts = ref([]);
const nextPageUrl = ref(null);
const loading = ref(false);

const observer = ref(null);
const bottom = ref(null);
const openMenus = ref({}); // для контроля меню

const toggleMenu = (postId) => {
  openMenus.value[postId] = !openMenus.value[postId];
};

const closeAllMenus = () => {
  openMenus.value = {};
};

const fetchPosts = async (
  url = `/api/users/${props.userId}/posts?per_page=4`
) => {
  if (loading.value || url === null) return;
  loading.value = true;
  try {
    const response = await axiosClient.get(url);
    if (Array.isArray(response.data?.data)) {
      posts.value.push(...response.data.data);
      nextPageUrl.value = response.data.next_page_url;
    }
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    const res = await axiosClient.get("/api/user");
    currentUserId.value = res.data.id;
  } catch (e) {
    console.error("Ошибка получения пользователя:", e);
  }

  await fetchPosts();
  await nextTick();

  observer.value = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && nextPageUrl.value) {
      fetchPosts(nextPageUrl.value);
    }
  });

  if (bottom.value) {
    observer.value.observe(bottom.value);
  }

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".menu-container")) {
      closeAllMenus();
    }
  });
});

const showDeleteModal = ref(false);
const deletingPost = ref(null);

const confirmDelete = (post) => {
  deletingPost.value = post;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  deletingPost.value = null;
};

const deletePost = async () => {
  if (!deletingPost.value) return;
  try {
    await axiosClient.delete(`/api/posts/${deletingPost.value.slug}`);
    posts.value = posts.value.filter((p) => p.id !== deletingPost.value.id);
  } catch (e) {
    console.error("Ошибка удаления поста", e);
  } finally {
    cancelDelete();
  }
};

const reportPost = (id) => {
  alert(`Пожаловаться на пост с ID ${id}`);
};
</script>

<template>
  <div class="space-y-6">
    <div
      v-for="post in posts"
      :key="post.id"
      class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 p-6 space-y-4 group">
      <!-- Автор и дата -->
      <div class="flex justify-between items-center relative">
        <div class="flex items-center space-x-3">
          <img
            :src="
              post.user?.profile?.avatar
                ? imageUrl(post.user.profile.avatar)
                : '/img/default-avatar.jpg'
            "
            class="w-10 h-10 rounded-full object-cover border-2 border-white shadow"
            alt="avatar" />
          <div>
            <RouterLink
              v-if="post.user?.profile?.slug"
              :to="`/profile/${post.user.profile.slug}`"
              class="font-semibold text-sm text-blue-600 hover:underline">
              {{ post.user.name }}
            </RouterLink>
            <div class="text-xs text-gray-500">
              {{
                formatDistanceToNow(new Date(post.created_at), {
                  addSuffix: true,
                  locale: ru,
                })
              }}
            </div>
          </div>
        </div>

        <div
          class="text-sm rounded-full text-gray-600 flex items-center gap-2 relative">
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

          <!-- Меню -->
          <div class="relative menu-container">
            <button
              @click.stop="toggleMenu(post.id)"
              class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-100 transition">
              <span class="text-xl leading-none">⋯</span>
            </button>

            <div
              v-if="openMenus[post.id]"
              class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
              <ul class="text-sm text-gray-700 py-1">
                <li v-if="post.user_id === currentUserId">
                  <button
                    @click="confirmDelete(post)"
                    class="w-full text-left px-4 py-2 hover:bg-red-100 text-red-600">
                    Удалить
                  </button>
                </li>
                <li>
                  <button
                    @click="reportPost(post.id)"
                    class="w-full text-left px-4 py-2 hover:bg-gray-100">
                    Пожаловаться
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Заголовок и контент -->
      <div class="space-y-3">
        <router-link
          :to="{ name: 'posts.show', params: { slug: post.slug } }"
          class="text-xl font-bold text-gray-900 hover:text-blue-600 transition block">
          {{ post.title }}
        </router-link>

        <div
          class="prose max-w-none text-gray-700 line-clamp-3"
          v-html="post.content"></div>
      </div>

      <!-- Галерея -->
      <div
        v-if="post.images?.length"
        class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-2 h-64 overflow-hidden rounded-xl">
        <div
          class="md:col-span-2 h-full"
          :class="{ 'md:col-span-3': post.images.length === 1 }">
          <img
            :src="imageUrl(post.images[0])"
            @click="openImage(post.images[0])"
            class="w-full h-full object-cover rounded-lg cursor-pointer hover:opacity-90 transition"
            :alt="post.title" />
        </div>
        <div
          v-if="post.images.length > 1"
          class="grid grid-cols-2 gap-3 h-full">
          <img
            v-for="(img, i) in post.images.slice(1, 5)"
            :key="i"
            :src="imageUrl(img)"
            @click="openImage(img)"
            class="w-full h-full object-cover rounded-lg cursor-pointer hover:opacity-90 transition"
            :alt="`Изображение ${i + 2} из поста ${post.title}`" />
          <div
            v-if="post.images.length > 5"
            class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center text-gray-700 font-medium cursor-pointer hover:bg-gray-200 transition group"
            @click="openImage(post.images[5])">
            <div class="text-center">
              <div class="text-2xl">+{{ post.images.length - 5 }}</div>
              <div class="text-xs mt-1">ещё фото</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Теги и действия -->
      <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
        <div v-if="post.tags?.length" class="flex flex-wrap gap-2">
          <router-link
            v-for="tag in post.tags"
            :key="tag.id"
            :to="{ name: 'tags.show', params: { slug: tag.slug } }"
            class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded-full hover:bg-blue-100 transition">
            #{{ tag.name }}
          </router-link>
        </div>
        <div class="flex items-center space-x-4">
          <router-link
            v-if="post.is_commentable"
            :to="{
              name: 'posts.show',
              params: { slug: post.slug },
              hash: '#comments',
            }"
            class="text-sm text-gray-600 hover:text-blue-600 hover:underline transition">
            {{ post.comments_count || 0 }} комментариев
          </router-link>
          <span v-else class="text-sm text-gray-400">Комментарии закрыты</span>
        </div>
      </div>

      <!-- Лайки/дизлайки -->
      <Actions
        :post-id="post.id"
        :initial-likes="post.like_count"
        :initial-dislikes="post.dislike_count"
        class="border-t border-gray-100 pt-3 mt-3" />
    </div>

    <!-- Лоадер -->
    <div v-if="loading" class="py-8">
      <div class="animate-pulse space-y-6">
        <div class="h-48 bg-gray-100 rounded-xl"></div>
        <div class="h-48 bg-gray-100 rounded-xl"></div>
      </div>
    </div>

    <!-- Точка триггера -->
    <div ref="bottom" class="h-1"></div>
  </div>

  <!-- Превью изображения -->
  <div
    v-if="previewImage"
    @click="previewImage = null"
    class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center cursor-zoom-out p-4">
    <div class="max-w-4xl w-full">
      <img
        :src="previewImage"
        class="w-full h-auto max-h-[90vh] object-contain rounded-lg shadow-2xl" />
    </div>
  </div>

  <Teleport to="body">
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-800">Удалить пост?</h2>
        <p class="text-gray-600">
          Вы действительно хотите удалить пост
          <span class="font-semibold">«{{ deletingPost?.title }}»</span>? Это
          действие необратимо.
        </p>
        <div class="flex justify-end gap-3 pt-4">
          <button
            @click="cancelDelete"
            class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">
            Отмена
          </button>
          <button
            @click="deletePost"
            class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white">
            Удалить
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.prose >>> img {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}
.prose >>> pre {
  background-color: #f8f8f8;
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
  margin: 0.5rem 0;
}
.prose >>> code {
  background-color: #f8f8f8;
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  font-family: monospace;
}
.prose >>> h1,
.prose >>> h2,
.prose >>> h3,
.prose >>> h4,
.prose >>> h5,
.prose >>> h6 {
  margin-top: 1em;
  margin-bottom: 0.5em;
  font-weight: 600;
}
.prose >>> p {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
.prose >>> ul,
.prose >>> ol {
  padding-left: 1.5em;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
.prose >>> blockquote {
  border-left: 4px solid #e5e7eb;
  padding-left: 1rem;
  margin-left: 0;
  color: #6b7280;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
