<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { RouterLink } from "vue-router";
import axios from "../../axios";
import debounce from "lodash/debounce";

const query = ref("");
const results = ref({ posts: [], users: [], categories: [], tags: [] });
const resultsVisible = ref(false);

const backendUrl = import.meta.env.VITE_BACKEND_URL;

const router = useRouter();

function goToProfile(slug) {
  resultsVisible.value = false;
  query.value = "";
  router.push({ name: "Profile", params: { slug } });
}

function goToPost(slug) {
  resultsVisible.value = false;
  query.value = "";
  router.push({ name: "PostsShow", params: { slug } });
}

function goToCategory(slug) {
  resultsVisible.value = false;
  query.value = "";
  router.push({ name: "CategoryShow", params: { slug } });
}

function goToTag(slug) {
  resultsVisible.value = false;
  query.value = "";
  router.push({ name: "TagShow", params: { slug } });
}

const debouncedSearch = debounce(async () => {
  if (query.value.length < 2) {
    resultsVisible.value = false;
    return;
  }

  try {
    const { data } = await axios.get("/api/search", {
      params: { q: query.value },
    });

    console.log("Результаты поиска:", data);

    results.value = data;
    resultsVisible.value = true;
  } catch (e) {
    console.error("Ошибка поиска:", e);
  }
}, 300);

const hasResults = computed(
  () => results.value.posts.length || results.value.users.length
);

function goToFullSearch() {
  resultsVisible.value = false;
  router.push({ name: "SearchFull", query: { q: query.value } });
}
</script>

<template>
  <div class="relative w-full max-w-2xl mx-auto">
    <input
      type="text"
      v-model="query"
      @input="debouncedSearch"
      placeholder="Поиск в Sova"
      class="text-white w-full px-5 py-2 rounded-lg border border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm" />

    <div
      v-if="resultsVisible"
      class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl mt-2 shadow-xl overflow-hidden">
      <div v-if="hasResults">
        <!-- Посты -->
        <div v-if="results.posts.length">
          <p class="text-xs text-gray-400 px-6 pt-4 uppercase tracking-wide">
            Посты
          </p>
          <a
            v-for="post in results.posts"
            :key="'p' + post.id"
            href="#"
            @click.prevent="goToPost(post.slug)"
            class="block px-6 py-3 hover:bg-gray-100 transition-colors">
            {{ post.title }}
          </a>
        </div>

        <!-- Пользователи -->
        <div v-if="results.users.length">
          <p
            class="text-xs text-gray-400 px-6 pt-3 pb-2 uppercase tracking-wide">
            Пользователи
          </p>
          <a
            v-for="user in results.users"
            :key="'u' + user.id"
            href="#"
            @click.prevent="goToProfile(user.profile.slug)"
            class="flex items-center gap-3 px-6 py-3 hover:bg-gray-100 transition-colors">
            <img
              :src="user.profile.image_url || '/img/default-avatar.jpg'"
              alt="User avatar"
              class="size-8 rounded-full object-cover" />
            <span class="text-gray-800">{{ user.name }}</span>
          </a>
        </div>
      </div>

      <!-- Категории -->
      <div v-if="results.categories.length">
        <p class="text-xs text-gray-400 px-6 pt-3 pb-2 uppercase tracking-wide">
          Категории
        </p>
        <a
          v-for="category in results.categories"
          :key="'c' + category.id"
          href="#"
          @click.prevent="goToCategory(category.slug)"
          class="block px-6 py-3 hover:bg-gray-100 transition-colors">
          {{ category.name }}
        </a>
      </div>

      <!-- Теги -->
      <div v-if="results.tags.length">
        <p class="text-xs text-gray-400 px-6 pt-3 pb-2 uppercase tracking-wide">
          Теги
        </p>
        <a
          v-for="tag in results.tags"
          :key="'t' + tag.id"
          href="#"
          @click.prevent="goToTag(tag.slug)"
          class="block px-6 py-3 hover:bg-gray-100 transition-colors">
          #{{ tag.name }}
        </a>
      </div>

      <!-- Пустой результат -->
      <div v-if="!hasResults" class="px-6 py-4 text-sm text-gray-500">
        Ничего не найдено.
        <button
          @click="goToFullSearch"
          class="ml-2 text-blue-600 hover:underline font-semibold">
          Искать "{{ query }}"
        </button>
      </div>
    </div>
  </div>
</template>
