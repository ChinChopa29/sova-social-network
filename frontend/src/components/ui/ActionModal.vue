<!-- components/modals/ActionModal.vue -->
<script setup>
import { reactive, ref, watch } from "vue";
import axiosClient from "../../axios";
import { useRouter } from "vue-router";

const router = useRouter();

const props = defineProps({
  show: Boolean,
  reportId: [String, Number],
  targetUserId: [String, Number],
  targetId: [String, Number],
  targetType: String,
});

const emit = defineEmits(["update:show", "success"]);

const form = reactive({
  action_type: "delete",
  comment: "",
  report_id: props.reportId,
  target_user_id: props.targetUserId,
  target_id: props.targetId,
  target_type: props.targetType,
});

const errorMessage = ref("");
const isLoading = ref(false);

const submitAction = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = "";

    await axiosClient.post("/api/report-actions", form);

    emit("success");
    emit("update:show", false);
    form.comment = "";
    await router.push({ name: "AdminReports" });
  } catch (e) {
    errorMessage.value = e.response?.data?.message || "Произошла ошибка";
  } finally {
    isLoading.value = false;
  }
};

const cancel = () => {
  errorMessage.value = "";
  emit("update:show", false);
};

watch(
  () => [props.reportId, props.targetUserId, props.targetId, props.targetType],
  ([reportId, targetUserId, targetId, targetType]) => {
    form.report_id = reportId;
    form.target_user_id = targetUserId;
    form.target_id = targetId;
    form.target_type = targetType;
  },
  { immediate: true }
);
</script>

<template>
  <Teleport to="body" v-if="show">
    <div
      class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-800">Принять меры</h2>
        <p class="text-sm text-gray-600">
          Выберите действие для данной жалобы и укажите комментарий
          (необязательно)
        </p>

        <form @submit.prevent="submitAction" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Тип действия</label
            >
            <select
              v-model="form.action_type"
              required
              class="py-2 px-4 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
              <option value="delete">Удалить контент</option>
              <option value="warning">Отправить предупреждение</option>
              <option value="ban">Заблокировать пользователя</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Комментарий (необязательно)</label
            >
            <textarea
              v-model="form.comment"
              rows="3"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200 py-2 px-4"
              placeholder="Укажите причину принятых мер..."></textarea>
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
              :disabled="isLoading"
              class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50">
              <span v-if="isLoading">Обработка...</span>
              <span v-else>Подтвердить</span>
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
