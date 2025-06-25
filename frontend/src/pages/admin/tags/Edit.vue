<script setup>
import { ref } from "vue";
import axiosClient from "../../../axios";

const props = defineProps({
  tag: Object,
});

const emit = defineEmits(["close", "success"]);

const form = ref({
  name: props.tag.name,
});

const errors = ref({});
const isSubmitting = ref(false);

const submit = async () => {
  isSubmitting.value = true;
  try {
    await axiosClient.put(`/api/tags/${props.tag.slug}`, form.value);
    emit("success", "Тег успешно обновлён");
    closeModal();
  } catch (error) {
    if (error.response?.status === 422) {
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
      v-if="tag"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="modal-box w-full max-w-md bg-white rounded-lg shadow-lg">
        <h3 class="font-bold text-lg">Редактировать тег</h3>

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
