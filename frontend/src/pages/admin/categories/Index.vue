<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRouter } from "vue-router";
import CreateCategories from "../categories/Create.vue";
import EditCategories from "../categories/Edit.vue";
import axiosClient from "../../../axios";
import Toast from "../../../components/ui/Toast.vue";

const activeModal = ref(null);
const toastMessage = ref("");
const toastVisible = ref(false);
const searchQuery = ref("");

const allCategories = ref([]);
const isFetchingCategories = ref(false);
const currentCategory = ref(null);

const currentPage = ref(1);
const itemsPerPage = 10;

const closeModal = () => {
  activeModal.value = null;
  currentCategory.value = null;
};

const handleSuccess = (message) => {
  toastMessage.value = message;
  toastVisible.value = true;
  fetchCategories();
};

const fetchCategories = async () => {
  isFetchingCategories.value = true;
  try {
    const res = await axiosClient.get("/api/categories");
    allCategories.value = res.data.data;
  } finally {
    isFetchingCategories.value = false;
  }
};

const filteredCategories = computed(() => {
  if (!searchQuery.value) return allCategories.value;

  const query = searchQuery.value.toLowerCase();
  return allCategories.value.filter(
    (category) =>
      category.name.toLowerCase().includes(query) ||
      (category.description &&
        category.description.toLowerCase().includes(query))
  );
});

const paginatedCategories = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredCategories.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() =>
  Math.ceil(filteredCategories.value.length / itemsPerPage)
);

watch(searchQuery, () => {
  currentPage.value = 1;
});

const editCategory = (category) => {
  currentCategory.value = category;
  activeModal.value = "edit-category";
};

const deleteCategory = async (slug) => {
  if (!confirm("Вы уверены, что хотите удалить эту категорию?")) return;

  try {
    await axiosClient.delete(`/api/categories/${slug}`);
    handleSuccess("Категория успешно удалена");
  } catch (error) {
    toastMessage.value = "Ошибка при удалении категории";
    toastVisible.value = true;
  }
};

const getParentName = (parentId) => {
  if (!parentId) return "-";
  const parent = allCategories.value.find((c) => c.id === parentId);
  return parent ? parent.name : "-";
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

const router = useRouter();

const goToCategory = (slug) => {
  router.push({ name: "AdminCategoryShow", params: { slug } });
};

onMounted(fetchCategories);
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Заголовок и поиск -->
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Категории</h1>
        <p class="text-sm text-gray-500">
          Управление категориями вашего магазина
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
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
            placeholder="Поиск категорий..."
            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full" />
        </div>

        <button
          @click="activeModal = 'category'"
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
          Новая категория
        </button>
      </div>
    </div>

    <!-- Таблица -->
    <div
      class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ID
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Название
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Родительская
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Действия
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="isFetchingCategories">
              <td colspan="4" class="px-6 py-4 text-center">
                <div class="flex justify-center items-center py-8">
                  <div
                    class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
                </div>
              </td>
            </tr>

            <tr v-else-if="paginatedCategories.length === 0">
              <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                <div
                  class="flex flex-col items-center justify-center space-y-2">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
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
                  <p class="text-lg font-medium">Категории не найдены</p>
                  <p class="text-sm">Попробуйте изменить параметры поиска</p>
                </div>
              </td>
            </tr>

            <tr
              v-for="category in paginatedCategories"
              :key="category.id"
              @click="goToCategory(category.slug)"
              class="hover:bg-gray-100 cursor-pointer">
              <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                {{ category.id }}
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ category.name }}
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  v-if="category.parent_id"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  {{ getParentName(category.parent_id) }}
                </span>
                <span v-else class="text-gray-500">—</span>
              </td>

              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <button
                    @click="editCategory(category)"
                    class="text-blue-600 hover:text-blue-900 transition-colors"
                    title="Редактировать">
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
                    @click="deleteCategory(category.slug)"
                    class="text-red-600 hover:text-red-900 transition-colors"
                    title="Удалить">
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

    <!-- Модальные окна -->
    <CreateCategories
      v-if="activeModal === 'category'"
      :show="true"
      @close="closeModal"
      @success="handleSuccess" />

    <EditCategories
      v-if="activeModal === 'edit-category' && currentCategory"
      :show="true"
      :category="currentCategory"
      :all-categories="allCategories"
      @close="closeModal"
      @success="handleSuccess" />

    <Toast :message="toastMessage" v-model:show="toastVisible" />
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
</template>
