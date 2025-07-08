<script setup>
import { reactive, watch, ref } from "vue";
import axiosClient from "../../axios";

const props = defineProps({
  show: Boolean,
  modelType: [String],
  modelId: [String, Number],
  targetUserId: [String, Number],
});

const errorMessage = ref("");

const emit = defineEmits(["update:show", "success"]);

const form = reactive({
  type: "",
  target_id: null,
  target_user_id: null,
  category: "",
  comment: "",
});

watch(
  () => props.modelType,
  (val) => {
    form.type = val;
  },
  { immediate: true }
);
watch(
  () => props.modelId,
  (val) => {
    form.target_id = val;
  },
  { immediate: true }
);
watch(
  () => props.targetUserId,
  (val) => {
    form.target_user_id = val;
  },
  { immediate: true }
);

const submitReport = async () => {
  try {
    errorMessage.value = "";

    await axiosClient.post("/api/reports", form);

    emit("success");
    emit("update:show", false);

    form.category = "";
    form.comment = "";
  } catch (e) {
    if (e.response?.status === 409) {
      errorMessage.value =
        e.response.data.message || "Вы уже отправляли жалобу.";
    } else if (e.response?.status === 422) {
      errorMessage.value = "Некорректные данные. Проверьте поля формы.";
    } else {
      errorMessage.value = "Произошла неизвестная ошибка. Попробуйте позже.";
    }
  }
};

const cancel = () => {
  errorMessage.value = "";
  emit("update:show", false);
};
</script>

<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-800">Пожаловаться</h2>
        <p class="text-sm text-gray-600">
          Укажите причину жалобы и, при необходимости, поясните ситуацию. Мы
          рассмотрим её в ближайшее время.
        </p>

        <form @submit.prevent="submitReport" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Категория жалобы</label
            >
            <select
              v-model="form.category"
              required
              class="py-2 px-4 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
              <option disabled value="">-- выберите категорию --</option>
              <option value="insult">Оскорбление</option>
              <option value="violence">Пропаганда насилия</option>
              <option value="spam">Спам / реклама</option>
              <option value="fraud">Мошенничество</option>
              <option value="harassment">Издевательство</option>
              <option value="other">Другое</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Комментарий (необязательно)</label
            >
            <textarea
              v-model="form.comment"
              rows="3"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200 py-2 px-4"></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              @click="cancel"
              class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">
              Отмена
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white">
              Отправить жалобу
            </button>
          </div>
          <p v-if="errorMessage" class="text-sm text-red-600 text-center">
            {{ errorMessage }}
          </p>
        </form>
      </div>
    </div>
  </Teleport>
</template>
