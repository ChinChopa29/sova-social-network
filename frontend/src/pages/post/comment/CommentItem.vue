<script setup>
import { ref, computed } from "vue";
import { RouterLink } from "vue-router";
import CommentItem from "./CommentItem.vue";
import { formatDistanceToNow } from "date-fns";
import { ru } from "date-fns/locale";

const backendUrl = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000";

const props = defineProps({
  comment: Object,
  depth: Number,
  replyTo: Object,
});
const emit = defineEmits(["reply", "cancel", "submit"]);

const getAvatarUrl = (avatar) => {
  if (!avatar) return "/img/default-avatar.jpg";
  return avatar.startsWith("http") ? avatar : `${backendUrl}${avatar}`;
};

const avatarUrl = computed(() =>
  getAvatarUrl(props.comment.user?.profile?.avatar)
);

const replyContent = ref("");

const formatTimeAgo = (date) =>
  formatDistanceToNow(new Date(date), { addSuffix: true, locale: ru });

const visibleChildrenCount = ref(0);
const isExpanded = ref(false);

const hasChildren = computed(() => props.comment.children?.length > 0);
const visibleChildren = computed(() =>
  props.comment.children?.slice(0, visibleChildrenCount.value)
);

const toggleReplies = () => {
  if (isExpanded.value) {
    visibleChildrenCount.value = 0;
    isExpanded.value = false;
  } else {
    visibleChildrenCount.value = Math.min(props.comment.children.length, 3);
    isExpanded.value = true;
  }
};

const showMore = () => {
  visibleChildrenCount.value = Math.min(
    props.comment.children.length,
    visibleChildrenCount.value + 3
  );
};

const isReplying = computed(() => props.replyTo?.id === props.comment.id);

const handleSubmit = () => {
  if (!replyContent.value.trim()) return;

  emit("submit", {
    parentId: props.comment.id,
    content: replyContent.value.trim(),
  });

  replyContent.value = "";
};
</script>

<template>
  <div
    :class="['mt-3', depth > 0 ? 'ml-6 border-l-2 border-gray-200 pl-4' : '']">
    <div class="bg-white rounded-lg p-3 shadow-sm">
      <div class="flex items-center space-x-3">
        <!-- Аватар -->
        <img
          :src="avatarUrl"
          alt="avatar"
          class="w-8 h-8 rounded-full object-cover border" />

        <div class="flex flex-col">
          <RouterLink
            v-if="comment.user?.profile?.slug"
            :to="`/profile/${comment.user.profile.slug}`"
            class="font-semibold text-sm text-blue-600 hover:underline">
            {{ comment.user.name }}
          </RouterLink>
          <span class="text-xs text-gray-500">
            {{ formatTimeAgo(comment.created_at) }}
          </span>
        </div>
      </div>

      <div class="mt-2 text-gray-800 whitespace-pre-line">
        {{ comment.content }}
      </div>

      <div class="mt-1 flex space-x-4 text-xs">
        <button
          v-if="depth === 0"
          @click="$emit('reply', comment)"
          class="text-blue-500 hover:text-blue-700">
          Ответить
        </button>

        <button
          v-if="hasChildren"
          @click="toggleReplies"
          class="text-gray-500 hover:text-gray-700">
          {{
            isExpanded
              ? "Скрыть ответы"
              : `Показать ответы (${comment.children.length})`
          }}
        </button>
      </div>
    </div>

    <!-- Форма ответа под комментарием -->
    <div
      v-if="isReplying"
      class="mt-2 bg-gray-50 p-3 rounded-lg border border-gray-200">
      <textarea
        v-model="replyContent"
        class="w-full border border-gray-300 rounded-lg p-2 mb-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-500"
        rows="2"
        placeholder="Ваш ответ..."
        autofocus />
      <div class="flex justify-end space-x-2">
        <button
          @click="$emit('cancel')"
          class="px-3 py-1 text-gray-500 hover:text-gray-700 text-sm">
          Отмена
        </button>
        <button
          @click="handleSubmit"
          class="px-4 py-1 text-white bg-blue-600 hover:bg-blue-700 rounded text-sm">
          Ответить
        </button>
      </div>
    </div>

    <!-- Дочерние комментарии -->
    <div v-if="isExpanded" class="mt-2 space-y-2">
      <CommentItem
        v-for="child in visibleChildren"
        :key="child.id"
        :comment="child"
        :depth="depth + 1"
        :reply-to="replyTo"
        @reply="$emit('reply', $event)"
        @cancel="$emit('cancel')"
        @submit="$emit('submit', $event)" />

      <button
        v-if="visibleChildrenCount < comment.children.length"
        @click="showMore"
        class="text-sm text-blue-500 hover:text-blue-700 ml-2">
        Показать ещё
        {{ comment.children.length - visibleChildrenCount }} ответ(а)
      </button>
    </div>
  </div>
</template>
