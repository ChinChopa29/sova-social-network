<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import axiosClient from "../../../axios";
import CommentItem from "./CommentItem.vue";

const props = defineProps({
  slug: { required: true },
  postId: { type: Number, required: true },
});

const comments = ref([]);
const content = ref("");
const replyTo = ref(null);
const replyContent = ref("");
let channel = null;

const handleReply = (comment) => {
  replyTo.value = comment;
};

const loadComments = async () => {
  try {
    const { data } = await axiosClient.get(`/api/posts/${props.slug}/comments`);
    comments.value = data; // без buildCommentTree!
  } catch (e) {
    console.error("Ошибка загрузки комментариев", e);
  }
};

const sendComment = async () => {
  if (!content.value.trim()) return;

  try {
    await axiosClient.post("/api/comments", {
      content: content.value,
      post_id: props.postId,
      parent_id: null,
    });

    content.value = "";
    await loadComments();
  } catch (e) {
    console.error(
      "Ошибка при отправке комментария",
      e.response?.data || e.message
    );
  }
};

const startReply = (commentId) => {
  replyTo.value = commentId;
  replyContent.value = "";
};

const cancelReply = () => {
  replyTo.value = null;
  replyContent.value = "";
};

const sendReply = async ({ parentId, content }) => {
  try {
    await axiosClient.post("/api/comments", {
      content,
      post_id: props.postId,
      parent_id: parentId,
    });

    replyTo.value = null;
    await loadComments();
  } catch (e) {
    console.error("Ошибка при отправке ответа", e.response?.data || e.message);
  }
};

onMounted(() => {
  loadComments();
  console.log("COMMENTS:", comments.value);
});

onBeforeUnmount(() => {
  if (channel) {
    window.Echo.leave(channel);
  }
});

watch(
  () => props.postId,
  async (newId, oldId) => {
    if (newId !== oldId) {
      await loadComments();
    }
  }
);
</script>

<template>
  <div class="mt-8 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Комментарии</h2>

    <!-- Форма нового комментария -->
    <form
      @submit.prevent="sendComment"
      class="mb-8 bg-white rounded-lg shadow p-4">
      <textarea
        v-model="content"
        class="w-full border border-gray-300 rounded-lg p-3 mb-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500"
        rows="3"
        placeholder="Напишите ваш комментарий..." />
      <div class="flex justify-end">
        <button
          type="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg transition duration-200">
          Отправить
        </button>
      </div>
    </form>

    <!-- Форма ответа -->
    <div v-if="replyTo" class="mb-6 bg-gray-50 rounded-lg shadow p-4">
      <textarea
        v-model="replyContent"
        class="w-full border border-gray-300 rounded-lg p-3 mb-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500"
        rows="3"
        placeholder="Напишите ваш ответ..."
        autofocus />
      <div class="flex justify-end space-x-3">
        <button
          @click="cancelReply"
          class="px-4 py-2 text-gray-600 hover:text-gray-800 transition duration-200">
          Отмена
        </button>
        <button
          @click="sendReply"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg transition duration-200">
          Отправить ответ
        </button>
      </div>
    </div>

    <!-- Список комментариев -->
    <div v-if="comments.length === 0" class="text-center py-8 text-gray-500">
      Пока нет комментариев. Будьте первым!
    </div>

    <div v-else class="space-y-4">
      <CommentItem
        v-for="comment in comments"
        :key="comment.id"
        :comment="comment"
        :depth="0"
        :reply-to="replyTo"
        @reply="startReply"
        @cancel="cancelReply"
        @submit="sendReply" />
    </div>
  </div>
</template>
