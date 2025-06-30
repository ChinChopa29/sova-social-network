<script setup>
import { ref } from "vue";
import axiosClient from "../../../axios";

const props = defineProps({
  show: Boolean,
  category: Object,
  allCategories: Array,
});

const emit = defineEmits(["close", "success"]);

const form = ref({
  name: props.category.name,
  description: props.category.description,
  parent_id: props.category.parent_id,
});

const errors = ref({});
const isSubmitting = ref(false);

const submit = async () => {
  isSubmitting.value = true;
  try {
    await axiosClient.put(`/api/categories/${props.category.slug}`, form.value);
    emit("success", "Категория успешно обновлена");
    closeModal();
  } catch (error) {
    if (error.response.status === 422) {
      errors.value = error.response.data.errors;
    }
  } finally {
    isSubmitting.value = false;
  }
};

const closeModal = () => {
  emit("close");
};
</script>

<template>
  <teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="modal-box w-full max-w-xl bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between">
          <h3 class="text-2xl font-bold text-gray-800">
            Редактировать категорию
          </h3>
          <button
            @click="emit('close')"
            class="text-gray-500 hover:text-gray-700 focus:outline-none cursor-pointer">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="py-4">
          <!-- Название -->
          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text">Название</span>
            </label>
            <input
              type="text"
              v-model="form.name"
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
              :class="{ 'input-error': errors.name }" />
            <span v-if="errors.name" class="text-error text-sm mt-1">{{
              errors.name[0]
            }}</span>
          </div>

          <!-- Описание -->
          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text">Описание</span>
            </label>
            <textarea
              v-model="form.description"
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
              :class="{ 'textarea-error': errors.description }"></textarea>
            <span v-if="errors.description" class="text-error text-sm mt-1">{{
              errors.description[0]
            }}</span>
          </div>

          <!-- Родительская -->
          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text">Родительская категория</span>
            </label>
            <select
              v-model="form.parent_id"
              class="w-full px-4 py-2 border rounded-lg">
              <option :value="null">Нет родительской категории</option>
              <option
                v-for="category in props.allCategories.filter(
                  (c) => c.id !== props.category.id
                )"
                :key="category.id"
                :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="modal-action flex justify-end gap-2">
          <button
            @click="submit"
            class="cursor-pointer px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 transition"
            :disabled="isSubmitting">
            <span v-if="isSubmitting" class="loading loading-spinner"></span>
            Сохранить
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>
