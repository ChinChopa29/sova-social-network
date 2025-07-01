<script setup>
import { ref, onMounted, computed, watch, nextTick } from "vue";
import { useTransliterate } from "../../composables/useTransliterate";
import axiosClient from "../../axios";

const props = defineProps({ show: Boolean });
const emit = defineEmits(["close", "success"]);

const { transliterate } = useTransliterate();

// Категории
const categories = ref([]);
const categorySearch = ref("");
const categoryDropdownOpen = ref(false);
const filteredCategories = computed(() => {
  const term = categorySearch.value.toLowerCase();
  return categories.value.filter((c) => c.name.toLowerCase().includes(term));
});

// Пост
const post = ref({
  title: "",
  content: "",
  category_id: "",
  status: "published",
  is_commentable: false,
  images: [],
  tags: [],
});

// Состояния
const previews = ref([]);
const errors = ref({});
const isLoading = ref(false);
const newTag = ref("");

// Теги с автопоиском
const tagSuggestions = ref([]);
const tagDropdownOpen = ref(false);

// ─── Категории ─────────────────────────────────────────────────────
onMounted(async () => {
  const res = await axiosClient.get("/api/categories");
  categories.value = res.data.data ?? res.data;
});

const handleCategoryFocus = () => {
  categoryDropdownOpen.value = true;
};

const handleCategoryBlur = () => {
  setTimeout(() => {
    categoryDropdownOpen.value = false;
  }, 100);
};

const selectCategory = (category) => {
  post.value.category_id = category.id;
  categorySearch.value = category.name;
  categoryDropdownOpen.value = false;
};

// ─── Теги ───────────────────────────────────────────────────────────
watch(newTag, async (value) => {
  const query = value.trim();

  if (!query) {
    tagSuggestions.value = [];
    tagDropdownOpen.value = false;
    return;
  }

  try {
    const response = await axiosClient.get("/api/tags/search", {
      params: { query },
    });

    const tags = response.data.data ?? response.data;

    tagSuggestions.value = tags.filter(
      (tag) => !post.value.tags.includes(tag.name)
    );

    tagDropdownOpen.value = tagSuggestions.value.length > 0;
  } catch {
    tagSuggestions.value = [];
    tagDropdownOpen.value = false;
  }
});

const addTag = () => {
  const tag = newTag.value.trim();
  if (!tag || post.value.tags.includes(tag) || post.value.tags.length >= 10)
    return;
  post.value.tags.push(tag);
  newTag.value = "";
  tagSuggestions.value = [];
  tagDropdownOpen.value = false;
};

const selectTag = (tag) => {
  if (post.value.tags.length >= 10) return;
  if (!post.value.tags.includes(tag.name)) {
    post.value.tags.push(tag.name);
  }
  newTag.value = "";
  tagSuggestions.value = [];
  tagDropdownOpen.value = false;
};

const removeTag = (index) => {
  post.value.tags.splice(index, 1);
};

// ─── Изображения ────────────────────────────────────────────────────
const handleImages = (e) => {
  const files = Array.from(e.target.files);
  const total = post.value.images.length + files.length;
  if (total > 10) return alert("Максимум 10 изображений");

  files.forEach((file) => {
    post.value.images.push(file);

    const reader = new FileReader();
    reader.onload = (event) => {
      previews.value.push({ name: file.name, url: event.target.result });
    };
    reader.readAsDataURL(file);
  });

  e.target.value = "";
};

const removeImage = (index) => {
  post.value.images.splice(index, 1);
  previews.value.splice(index, 1);
};

// ─── Сброс формы ────────────────────────────────────────────────────
const resetForm = () => {
  post.value = {
    title: "",
    content: "",
    category_id: "",
    status: "published",
    is_commentable: false,
    images: [],
    tags: [],
  };
  previews.value = [];
  errors.value = {};
  categorySearch.value = "";
  newTag.value = "";
  tagSuggestions.value = [];
  tagDropdownOpen.value = false;
};

// ─── Отправка ───────────────────────────────────────────────────────
const submitPost = async () => {
  isLoading.value = true;
  errors.value = {};

  try {
    const formData = new FormData();

    post.value.tags.forEach((tag) => {
      formData.append("tags[]", tag);
    });

    for (const key in post.value) {
      if (key === "images") {
        post.value.images.forEach((img) => formData.append("images[]", img));
      } else if (key !== "tags") {
        formData.append(
          key,
          typeof post.value[key] === "boolean"
            ? post.value[key]
              ? "1"
              : "0"
            : post.value[key]
        );
      }
    }

    const response = await axiosClient.post("/api/posts", formData);
    emit("success", response.data.message);
    resetForm();
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      console.error("Ошибка при создании поста:", error);

      if (error.response) {
        const { status, data } = error.response;
        alert(`Ошибка ${status}: ${data.message || JSON.stringify(data)}`);
      } else if (error.request) {
        alert("Нет ответа от сервера. Проверь соединение.");
      } else {
        alert("Ошибка: " + error.message);
      }
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/40 z-50 flex items-start py-5 justify-center overflow-auto">
    <div
      class="bg-white p-6 rounded-lg shadow-lg w-full max-w-3xl max-h-[95vh] overflow-y-auto relative">
      <button
        @click="emit('close')"
        class="absolute top-2 right-2 text-gray-500 hover:text-black text-xl">
        &times;
      </button>
      <h2 class="text-xl font-semibold mb-4">Создать пост</h2>

      <form
        @submit.prevent="submitPost"
        class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2 space-y-1">
          <label class="block text-sm font-medium">Заголовок</label>
          <input
            v-model="post.title"
            class="w-full border p-2 rounded text-sm"
            required />
        </div>

        <div class="col-span-2 space-y-1">
          <label class="block text-sm font-medium">Содержимое</label>
          <textarea
            v-model="post.content"
            class="w-full border p-2 rounded text-sm min-h-[120px]"
            required></textarea>
        </div>

        <div class="col-span-2 space-y-1">
          <label class="block text-sm font-medium">Изображения (до 10)</label>
          <input
            type="file"
            multiple
            @change="handleImages"
            accept="image/*"
            class="w-full border p-1 rounded text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
          <div
            class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2 mt-2 max-h-[150px] overflow-y-auto">
            <div
              v-for="(image, index) in previews"
              :key="image.name"
              class="relative group h-24">
              <img
                :src="image.url"
                alt="preview"
                class="w-full h-full object-cover rounded" />
              <button
                type="button"
                @click="removeImage(index)"
                class="absolute top-1 right-1 bg-red-500 text-white text-xs px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">
                ✖
              </button>
            </div>
          </div>
        </div>

        <div class="col-span-2 relative space-y-1">
          <label class="block text-sm font-medium">Категория</label>
          <input
            type="text"
            v-model="categorySearch"
            placeholder="Поиск категории..."
            class="w-full border p-2 rounded text-sm"
            @focus="handleCategoryFocus"
            @blur="handleCategoryBlur" />

          <div
            v-if="categoryDropdownOpen && filteredCategories.length"
            class="absolute bg-white z-50 border rounded w-full mt-1 max-h-[150px] overflow-auto shadow text-sm">
            <div
              v-for="category in filteredCategories"
              :key="category.id"
              @mousedown.prevent="selectCategory(category)"
              class="px-3 py-1.5 hover:bg-blue-100 cursor-pointer"
              :class="{
                'bg-blue-50 font-semibold': category.id === post.category_id,
              }">
              {{ category.name }}
            </div>
          </div>

          <p v-if="errors.category_id" class="text-red-500 text-xs mt-1">
            {{ errors.category_id }}
          </p>
        </div>

        <div class="col-span-2 space-y-1">
          <label class="block text-sm font-medium">Теги (до 10)</label>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="(tag, index) in post.tags"
              :key="tag"
              class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs flex items-center">
              {{ tag }}
              <button
                type="button"
                class="ml-1 text-xs text-red-500 hover:text-red-700"
                @click="removeTag(index)">
                ✕
              </button>
            </span>
          </div>
          <div class="relative">
            <input
              v-model="newTag"
              @keydown.enter.prevent="addTag"
              placeholder="Введите тег и нажмите Enter"
              class="w-full border p-2 rounded text-sm mt-1"
              autocomplete="off" />

            <!-- Выпадающий список -->
            <div
              v-if="tagDropdownOpen"
              class="absolute z-10 bg-white border rounded mt-1 w-full max-h-40 overflow-y-auto shadow text-sm">
              <div
                v-for="suggestion in tagSuggestions"
                :key="suggestion.id"
                @mousedown.prevent="selectTag(suggestion)"
                class="px-3 py-2 hover:bg-blue-100 cursor-pointer">
                {{ suggestion.name }}
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-1">
          <label class="block text-sm font-medium">Статус</label>
          <select
            v-model="post.status"
            class="w-full border p-2 rounded text-sm">
            <option value="draft">Черновик</option>
            <option value="published">Опубликован</option>
            <option value="archived">Архив</option>
            <option value="private">Приватный</option>
          </select>
        </div>

        <div class="flex items-center space-x-2 self-end">
          <input
            id="is_commentable"
            type="checkbox"
            v-model="post.is_commentable"
            class="rounded h-4 w-4" />
          <label for="is_commentable" class="text-sm">
            Разрешить комментарии
          </label>
        </div>

        <div class="col-span-2 mt-2">
          <button
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-full text-sm"
            :disabled="isLoading">
            {{ isLoading ? "Сохранение..." : "Опубликовать" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
input[type="file"]::-webkit-file-upload-button {
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
}
</style>
