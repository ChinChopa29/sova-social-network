<script setup>
import { ref, onMounted, computed, watch } from "vue";
import axiosClient from "../../../axios";

const posts = ref([]);
const isFetchingPosts = ref(false);
const searchQuery = ref("");
const currentPage = ref(1);
const itemsPerPage = 10;

const fetchPosts = async () => {
  isFetchingPosts.value = true;
  try {
    const res = await axiosClient.get("/api/posts");
    posts.value = res.data.data;
  } finally {
    isFetchingPosts.value = false;
  }
};

onMounted(fetchPosts);

const paginatedPosts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredPosts.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() =>
  Math.ceil(filteredPosts.value.length / itemsPerPage)
);

watch(searchQuery, () => {
  currentPage.value = 1;
});

const selectedUser = ref("");
const selectedCategory = ref("");
const selectedStatus = ref("");

const uniqueUsers = computed(() => [
  ...new Set(posts.value.map((p) => p.user?.name).filter(Boolean)),
]);

const uniqueCategories = computed(() => [
  ...new Set(posts.value.map((p) => p.category?.name).filter(Boolean)),
]);

const filteredPosts = computed(() => {
  let list = posts.value;

  if (selectedUser.value)
    list = list.filter((p) => p.user?.name === selectedUser.value);
  if (selectedCategory.value)
    list = list.filter((p) => p.category?.name === selectedCategory.value);
  if (selectedStatus.value)
    list = list.filter((p) => p.status === selectedStatus.value);

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((post) =>
      [
        post.id?.toString(),
        post.title,
        post.user?.name,
        post.category?.name,
        post.status,
        new Date(post.created_at).toLocaleDateString("ru-RU"),
      ]
        .filter(Boolean)
        .some((field) => field.toLowerCase().includes(q))
    );
  }

  return list;
});

const paginationList = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const pages = [];

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (current > 4) pages.push("...");
    const start = Math.max(2, current - 2);
    const end = Math.min(total - 1, current + 2);
    for (let i = start; i <= end; i++) pages.push(i);
    if (end < total - 1) pages.push("...");
    pages.push(total);
  }

  return pages;
});

const changePage = (page) => {
  if (page !== "..." && page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Заголовок и поиск -->
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Посты</h1>
        <p class="text-sm text-gray-500">Просмотр всех постов</p>
      </div>

      <div class="relative w-full sm:w-64">
        <div
          class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg
            class="h-5 w-5 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Поиск по полям..."
          class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full" />
      </div>
    </div>

    <!-- Таблица -->
    <div
      class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <div
          class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-wrap gap-4 items-center">
          <div>
            <label
              class="block text-xs font-medium text-gray-500 uppercase mb-1"
              >Автор</label
            >
            <select
              v-model="selectedUser"
              class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4">
              <option value="">Все авторы</option>
              <option v-for="name in uniqueUsers" :key="name" :value="name">
                {{ name }}
              </option>
            </select>
          </div>

          <div>
            <label
              class="block text-xs font-medium text-gray-500 uppercase mb-1"
              >Категория</label
            >
            <select
              v-model="selectedCategory"
              class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4">
              <option value="">Все категории</option>
              <option v-for="cat in uniqueCategories" :key="cat" :value="cat">
                {{ cat }}
              </option>
            </select>
          </div>

          <div>
            <label
              class="block text-xs font-medium text-gray-500 uppercase mb-1"
              >Статус</label
            >
            <select
              v-model="selectedStatus"
              class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-2">
              <option value="">Все статусы</option>
              <option value="draft">Черновик</option>
              <option value="published">Опубликован</option>
              <option value="private">Приватный</option>
              <option value="archive">Архив</option>
            </select>
          </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ID
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Заголовок
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Автор
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Категория
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Статус
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Создан
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="isFetchingPosts">
              <td colspan="6" class="px-6 py-8 text-center">
                <div class="flex justify-center items-center h-20">
                  <div
                    class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
                </div>
              </td>
            </tr>

            <tr v-else-if="paginatedPosts.length === 0">
              <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                Посты не найдены
              </td>
            </tr>

            <tr
              v-for="post in paginatedPosts"
              :key="post.id"
              class="hover:bg-gray-50 cursor-pointer"
              @click="
                $router.push({
                  name: 'PostsShow',
                  params: { slug: post.slug },
                })
              ">
              <td class="px-6 py-4 text-sm text-gray-900">{{ post.id }}</td>
              <td class="px-6 py-4 text-sm font-medium text-blue-600 underline">
                {{ post.title }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">
                {{ post.user?.name || "—" }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">
                {{ post.category?.name || "—" }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">{{ post.status }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ new Date(post.created_at).toLocaleDateString() }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Пагинация -->
    <div class="flex justify-center items-center gap-2 my-4">
      <button
        @click="changePage(currentPage - 1)"
        :disabled="currentPage === 1"
        class="px-3 py-1 rounded border text-sm"
        :class="
          currentPage === 1
            ? 'text-gray-400 border-gray-300 cursor-not-allowed'
            : 'hover:bg-gray-100 border-gray-400'
        ">
        ←
      </button>

      <div v-for="page in paginationList" :key="page">
        <button
          v-if="page !== '...'"
          @click="changePage(page)"
          class="px-3 py-1 rounded border text-sm min-w-[36px]"
          :class="
            currentPage === page
              ? 'bg-blue-600 text-white border-blue-600'
              : 'hover:bg-gray-100 border-gray-400'
          ">
          {{ page }}
        </button>
        <span v-else class="px-2 text-gray-500">…</span>
      </div>

      <button
        @click="changePage(currentPage + 1)"
        :disabled="currentPage === totalPages"
        class="px-3 py-1 rounded border text-sm"
        :class="
          currentPage === totalPages
            ? 'text-gray-400 border-gray-300 cursor-not-allowed'
            : 'hover:bg-gray-100 border-gray-400'
        ">
        →
      </button>
    </div>
  </div>
</template>
