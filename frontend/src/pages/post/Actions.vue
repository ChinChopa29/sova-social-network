<script setup>
import { ref, watch } from "vue";
import axios from "../../axios";

const props = defineProps({
  postId: Number,
  initialLikes: Number,
  initialDislikes: Number,
});

const likeCount = ref(props.initialLikes);
const dislikeCount = ref(props.initialDislikes);
const userReaction = ref(null);

// Загружаем реакцию
const fetchUserReaction = async () => {
  try {
    if (!props.postId) return;

    const response = await axios.get(`/api/reactions/${props.postId}/my`);
    userReaction.value = response.data.reaction;
  } catch (err) {
    console.error("Ошибка загрузки реакции", err);
  }
};

// Следим за props.postId — как только он появится, загружаем
watch(
  () => props.postId,
  (newId) => {
    if (newId) {
      fetchUserReaction();
    }
  },
  { immediate: true }
);

// Обработка реакции
const react = async (type) => {
  try {
    const response = await axios.post(`/api/reactions`, {
      post_id: props.postId,
      type: type,
    });

    likeCount.value = response.data.like_count;
    dislikeCount.value = response.data.dislike_count;
    userReaction.value = response.data.user_reaction;
  } catch (err) {
    console.error("Ошибка при отправке реакции", err);
  }
};
</script>

<template>
  <div class="flex justify-center gap-8 py-4 text-gray-500">
    <!-- 👍 Like -->
    <button
      @click="react('like')"
      :class="[
        'flex items-center gap-2 transition',
        userReaction === 'like' ? 'text-green-600' : 'hover:text-green-600',
      ]"
      title="Нравится">
      <i class="fas fa-thumbs-up text-xl"></i>
      <span class="text-sm">{{ likeCount }}</span>
    </button>

    <!-- 👎 Dislike -->
    <button
      @click="react('dislike')"
      :class="[
        'flex items-center gap-2 transition',
        userReaction === 'dislike' ? 'text-red-600' : 'hover:text-red-600',
      ]"
      title="Не нравится">
      <i class="fas fa-thumbs-down text-xl"></i>
      <span class="text-sm">{{ dislikeCount }}</span>
    </button>
  </div>
</template>
