<script setup>
import { useForm } from "../../../composables/useForm";

const props = defineProps({ show: Boolean });
const emit = defineEmits(["close", "success"]);

const {
  data: tag,
  errors,
  isLoading,
  resetForm,
  submit,
} = useForm({
  endpoint: "/api/tags",
  defaultData: { name: "" },
  successMessage: "Тэг успешно создан!",
});

const submitTag = () => {
  submit((message) => emit("success", message));
};
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-bold text-gray-800">Создать тэг</h2>
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

        <form @submit.prevent="submitTag">
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Название</label>
            <input
              v-model="tag.name"
              type="text"
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
              :class="{ 'border-red-500': errors.name }" />
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">
              {{ errors.name }}
            </p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="submit"
              class="cursor-pointer px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 transition"
              :disabled="isLoading">
              <span v-if="isLoading">
                <svg
                  class="animate-spin h-5 w-5 inline mr-2"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24">
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Создание...
              </span>
              <span v-else>Создать</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
