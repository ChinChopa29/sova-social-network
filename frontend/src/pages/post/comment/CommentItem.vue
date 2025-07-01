<!-- src/components/CommentItem.vue -->
<template>
  <div
    :class="['mt-3', depth > 0 ? 'ml-6 border-l-2 border-gray-200 pl-4' : '']">
    <div class="bg-white rounded-lg p-3 shadow-sm">
      <div class="flex items-center space-x-2">
        <div class="font-semibold text-sm text-gray-800">
          {{ comment.user?.name || "Гость" }}
        </div>
        <span class="text-xs text-gray-500">
          {{ new Date(comment.created_at).toLocaleString() }}
        </span>
      </div>
      <div class="mt-1 text-gray-800">{{ comment.content }}</div>
      <button
        @click="$emit('reply', comment.id)"
        class="mt-1 text-xs text-blue-500 hover:text-blue-700">
        Ответить
      </button>
    </div>

    <div v-if="comment.children && comment.children.length" class="space-y-2">
      <CommentItem
        v-for="child in comment.children"
        :key="child.id"
        :comment="child"
        :depth="depth + 1"
        @reply="$emit('reply', $event)" />
    </div>
  </div>
</template>

<script setup>
import CommentItem from "./CommentItem.vue"; // рекурсивно
defineProps({
  comment: Object,
  depth: Number,
});
defineEmits(["reply"]);
</script>
