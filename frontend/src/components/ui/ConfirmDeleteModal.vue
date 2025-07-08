<script setup>
defineProps({
  modelValue: Boolean,
  title: {
    type: String,
    default: "Удалить элемент?",
  },
  itemName: {
    type: String,
    default: "",
  },
});
const emit = defineEmits(["confirm", "update:modelValue"]);

const handleConfirm = () => {
  emit("confirm");
  emit("update:modelValue", false);
};
</script>

<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-800">{{ title }}</h2>
        <p class="text-gray-600">
          Вы действительно хотите удалить
          <span class="font-semibold">«{{ itemName }}»</span>? Это действие
          необратимо.
        </p>
        <div class="flex justify-end gap-3 pt-4">
          <button
            @click="$emit('update:modelValue', false)"
            class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">
            Отмена
          </button>
          <button
            @click="handleConfirm"
            class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white">
            Удалить
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
