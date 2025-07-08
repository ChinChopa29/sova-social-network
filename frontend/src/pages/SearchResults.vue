<script setup>
import { onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import axios from "../axios";

const route = useRoute();
const query = ref(route.query.q || "");
const results = ref({
  posts: [],
  users: [],
  categories: [],
  tags: [],
  groups: [],
});

watch(
  () => route.query.q,
  (newQ) => {
    query.value = newQ;
    performSearch();
  }
);

onMounted(performSearch);

async function performSearch() {
  if (!query.value) return;

  const { data } = await axios.get("/api/search/full", {
    params: { q: query.value },
  });

  results.value = data;
}
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-semibold">Результаты поиска по "{{ query }}"</h1>

    <div v-if="results.posts.length">
      <h2 class="font-bold text-lg">Посты</h2>
      <ul>
        <li v-for="p in results.posts" :key="p.id">
          <RouterLink :to="{ name: 'PostsShow', params: { slug: p.slug } }">
            {{ p.title }}
          </RouterLink>
        </li>
      </ul>
    </div>

    <div v-if="results.users.length">
      <h2 class="font-bold text-lg">Пользователи</h2>
      <ul>
        <li v-for="u in results.users" :key="u.id">
          <RouterLink
            :to="{ name: 'Profile', params: { slug: u.profile.slug } }">
            {{ u.name }}
          </RouterLink>
        </li>
      </ul>
    </div>
  </div>
</template>
