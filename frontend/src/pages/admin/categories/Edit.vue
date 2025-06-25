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
      <div class="modal-box w-full max-w-xl bg-white rounded-lg shadow-lg">
        <h3 class="font-bold text-lg">Редактировать категорию</h3>

        <div class="py-4">
          <!-- Название -->
          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text">Название</span>
            </label>
            <input
              type="text"
              v-model="form.name"
              class="input input-bordered w-full"
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
              class="textarea textarea-bordered w-full h-24"
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
              class="select select-bordered w-full">
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
          <button @click="closeModal" class="btn">Отмена</button>
          <button
            @click="submit"
            class="btn btn-primary"
            :disabled="isSubmitting">
            <span v-if="isSubmitting" class="loading loading-spinner"></span>
            Сохранить
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>
