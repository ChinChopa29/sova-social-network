<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axiosClient from "../../../axios";
import ActionModal from "../../../components/ui/ActionModal.vue";

const route = useRoute();
const router = useRouter();
const report = ref(null);
const previewImage = ref(null);
const showActionModal = ref(false);
const isLoading = ref(false);

const targetType = computed(() => {
  return report.value?.target_type?.split("\\").pop().toLowerCase();
});

const imageUrl = (img) => {
  const base = import.meta.env.VITE_BACKEND_URL;
  const path = typeof img === "string" ? img : img?.path || "";
  return path.startsWith("/storage")
    ? `${base}${path}`
    : `${base}/storage/${path}`;
};

const openImage = (img) => {
  previewImage.value = imageUrl(img);
};

const rejectReport = async () => {
  if (!confirm("Вы уверены, что хотите отклонить эту жалобу?")) return;

  try {
    isLoading.value = true;
    await axiosClient.delete(`/api/reports/${report.value.id}`);
    router.push({ name: "AdminReports" });
  } catch (error) {
    alert("Не удалось отклонить жалобу. Пожалуйста, попробуйте еще раз.");
  } finally {
    isLoading.value = false;
  }
};

const handleActionSuccess = () => {
  router.push({ name: "AdminReports" });
};

const categoryLabel = {
  spam: "Спам/реклама",
  fraud: "Мошенничество",
  insult: "Оскорбление",
  violence: "Пропаганда насилия",
  harassment: "Издевательство",
  false_info: "Ложная информация",
  other: "Другое",
};

onMounted(async () => {
  try {
    const { id } = route.params;
    const response = await axiosClient.get(`/api/reports/${id}`);
    report.value = response.data;
  } catch (error) {
    router.push({ name: "AdminReports" });
  }
});
</script>

<template>
  <div v-if="report" class="max-w-6xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4 mb-6">
      <button
        @click="$router.back()"
        class="flex items-center gap-1 text-blue-600 hover:underline transition">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor">
          <path
            fill-rule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
            clip-rule="evenodd" />
        </svg>
        Назад к списку
      </button>
      <div class="flex items-center gap-3">
        <span
          class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
          {{ targetType === "post" ? "Пост" : targetType }}
        </span>
        <span
          class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
          Жалоба #{{ report.id }}
        </span>
      </div>
    </div>

    <!-- Report info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Информация о жалобе</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
          <h3 class="text-sm font-medium text-gray-500">Дата создания</h3>
          <p class="text-gray-900">
            {{ new Date(report.created_at).toLocaleString() }}
          </p>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-500">Категория жалобы</h3>
          <p class="text-gray-900 capitalize">
            {{ categoryLabel[report.category] || report.category }}
          </p>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-500">Статус</h3>
          <p class="text-gray-900">{{ report.status || "Новая" }}</p>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-500">Автор жалобы</h3>
          <p class="text-gray-900">{{ report.user?.name || "Аноним" }}</p>
        </div>
      </div>

      <div class="mb-4">
        <h3 class="text-sm font-medium text-gray-500">Комментарий к жалобе</h3>
        <p class="text-gray-900 p-3 bg-gray-50 rounded-lg mt-1">
          {{ report.comment || "Комментарий отсутствует" }}
        </p>
      </div>
    </div>

    <!-- Reported content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Объект жалобы</h2>

      <div v-if="targetType === 'post' && report.target" class="space-y-4">
        <div class="flex items-start gap-4">
          <div class="flex-1">
            <h3 class="text-lg font-semibold">{{ report.target.title }}</h3>
            <div class="prose max-w-none mt-2">
              {{ report.target.content }}
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-3">
              <span
                v-if="report.target?.category"
                class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full text-xs">
                {{ report.target.category.name }}
              </span>

              <router-link
                v-for="tag in report.target?.tags"
                :key="tag.id"
                :to="{ name: 'TagsShow', params: { slug: tag.slug } }"
                class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-full text-xs transition">
                #{{ tag.name }}
              </router-link>
            </div>
          </div>

          <router-link
            v-if="report.target.user?.profile"
            :to="{
              name: 'Profile',
              params: { slug: report.target.user.profile.slug },
            }"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-blue-600 transition">
            <img
              v-if="report.target.user.profile.avatar"
              :src="imageUrl(report.target.user.profile.avatar)"
              class="w-8 h-8 rounded-full object-cover"
              :alt="report.target.user.name" />
            <span>{{ report.target.user.name }}</span>
          </router-link>
        </div>

        <!-- Gallery -->
        <div
          v-if="report.target.images && report.target.images.length"
          class="mt-6">
          <h4 class="text-sm font-medium text-gray-500 mb-2">
            Изображения в посте
          </h4>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <div
              v-for="(img, index) in report.target.images"
              :key="index"
              class="relative group overflow-hidden rounded-lg border border-gray-200">
              <img
                :src="imageUrl(img)"
                @click="openImage(img)"
                :alt="img.alt || 'Изображение поста'"
                class="w-full h-40 object-cover cursor-zoom-in hover:opacity-90 transition-opacity" />
              <div
                class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2 pointer-events-none">
                <span class="text-white text-xs">Нажмите для просмотра</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else>
        <p class="text-gray-500">Неизвестный тип контента</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold mb-4">Действия</h2>

      <div class="flex flex-wrap gap-3">
        <button
          @click="showActionModal = true"
          class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center gap-2">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            viewBox="0 0 20 20"
            fill="currentColor">
            <path
              fill-rule="evenodd"
              d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
              clip-rule="evenodd" />
          </svg>
          Принять меры
        </button>

        <button
          @click="rejectReport"
          :disabled="isLoading"
          class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition flex items-center gap-2 disabled:opacity-50">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            viewBox="0 0 20 20"
            fill="currentColor">
            <path
              fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd" />
          </svg>
          Отклонить жалобу
        </button>
      </div>
    </div>

    <!-- Image preview modal -->
    <div
      v-if="previewImage"
      @click="previewImage = null"
      class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 cursor-zoom-out transition-opacity duration-300">
      <div class="max-w-6xl w-full">
        <img
          :src="previewImage"
          alt="Просмотр изображения"
          class="w-full h-auto max-h-[90vh] object-contain rounded-lg shadow-2xl" />
      </div>
    </div>

    <div v-else-if="report.target_type === 'comment' && report.target">
      <h3 class="text-lg font-semibold">Комментарий</h3>
      <p>{{ report.target.content }}</p>
      <p class="text-sm text-gray-500">Автор: {{ report.target.user?.name }}</p>
    </div>

    <!-- Action modal -->
    <ActionModal
      v-if="report.target"
      :show="showActionModal"
      :report-id="report.id"
      :target-user-id="report.target.user?.id"
      :target-id="report.target.id"
      :target-type="targetType"
      @update:show="showActionModal = $event"
      @success="handleActionSuccess" />
  </div>
</template>
