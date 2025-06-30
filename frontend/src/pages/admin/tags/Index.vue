<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRouter } from "vue-router";
import CreateTags from "../tags/Create.vue";
import axiosClient from "../../../axios";
import EditTags from "./Edit.vue";
import Toast from "../../Toast.vue";

const allTags = ref([]);
const isFetchingTags = ref(false);
const searchQuery = ref("");
const currentTag = ref(null);
const activeModal = ref(null);
const toastMessage = ref("");
const toastVisible = ref(false);

const currentPage = ref(1);
const itemsPerPage = 10;

const fetchTags = async () => {
  isFetchingTags.value = true;
  try {
    const res = await axiosClient.get("/api/tags");
    allTags.value = res.data.data;
  } finally {
    isFetchingTags.value = false;
  }
};

onMounted(fetchTags);

const filteredTags = computed(() => {
  if (!searchQuery.value) return allTags.value;

  const query = searchQuery.value.toLowerCase();
  return allTags.value.filter((tag) => tag.name.toLowerCase().includes(query));
});

const paginatedTags = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredTags.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() =>
  Math.ceil(filteredTags.value.length / itemsPerPage)
);

watch(searchQuery, () => {
  currentPage.value = 1;
});

const editTag = (tag) => {
  currentTag.value = tag;
  activeModal.value = "edit-tag";
};

const deleteTag = async (slug) => {
  if (!confirm("Вы уверены, что хотите удалить этот тег?")) return;

  try {
    await axiosClient.delete(`/api/tags/${slug}`);
    toastMessage.value = "Тег успешно удалён";
    toastVisible.value = true;
    fetchTags();
  } catch {
    toastMessage.value = "Ошибка при удалении тега";
    toastVisible.value = true;
  }
};

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

const closeModal = () => {
  currentTag.value = null;
  activeModal.value = null;
};

const handleSuccess = (message) => {
  toastMessage.value = message;
  toastVisible.value = true;
  fetchTags();
};

const router = useRouter();

const goToTag = (slug) => {
  router.push({ name: "AdminTagShow", params: { slug } });
};
</script>

<template>
  <div class="flex flex-col gap-6">
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Теги</h1>
        <p class="text-sm text-gray-500">Управление тегами и их авторами</p>
      </div>

      <div class="w-full sm:w-auto relative sm:flex gap-3">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Поиск тегов..."
          class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full sm:w-64" />
        <div
          class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
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
        <button
          @click="activeModal = 'tag'"
          class="flex items-center gap-2 cursor-pointer px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 transition">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            viewBox="0 0 20 20"
            fill="currentColor">
            <path
              fill-rule="evenodd"
              d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
              clip-rule="evenodd" />
          </svg>
          Новый тег
        </button>
      </div>
    </div>

    <div
      class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                ID
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Название
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Автор
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                Действия
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="isFetchingTags">
              <td colspan="3" class="px-6 py-4 text-center">
                <div class="flex justify-center py-8">
                  <div
                    class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
                </div>
              </td>
            </tr>

            <tr v-else-if="paginatedTags.length === 0">
              <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                <div class="flex flex-col items-center space-y-2">
                  <svg
                    class="h-12 w-12 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="text-lg font-medium">Теги не найдены</p>
                  <p class="text-sm">Измени параметры поиска</p>
                </div>
              </td>
            </tr>

            <tr
              v-for="tag in paginatedTags"
              :key="tag.id"
              @click="goToTag(tag.slug)"
              class="hover:bg-gray-100 cursor-pointer">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ tag.id }}
              </td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ tag.name }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ tag.user?.name || "—" }}
              </td>
              <td class="px-6 py-4 text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button
                    @click.stop="editTag(tag)"
                    title="Редактировать"
                    class="text-blue-600 hover:text-blue-900 transition-colors">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      viewBox="0 0 20 20"
                      fill="currentColor">
                      <path
                        d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                  </button>
                  <button
                    @click.stop="deleteTag(tag.slug)"
                    title="Удалить"
                    class="text-red-600 hover:text-red-900 transition-colors">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      viewBox="0 0 20 20"
                      fill="currentColor">
                      <path
                        fill-rule="evenodd"
                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center gap-2 mt-6">
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

      <template v-for="page in paginationList" :key="page">
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
      </template>

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

    <!-- Модальное окно -->
    <CreateTags
      v-if="activeModal === 'tag'"
      :show="true"
      @close="closeModal"
      @success="handleSuccess" />

    <EditTags
      v-if="activeModal === 'edit-tag' && currentTag"
      :tag="currentTag"
      @close="closeModal"
      @success="handleSuccess" />

    <Toast :message="toastMessage" v-model:show="toastVisible" />
  </div>
</template>
