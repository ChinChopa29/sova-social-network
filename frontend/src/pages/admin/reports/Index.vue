<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import { RouterLink } from "vue-router";
import axiosClient from "../../../axios";
import Toast from "../../../components/ui/Toast.vue";

const toastMessage = ref("");
const toastVisible = ref(false);

const showToast = (message) => {
  toastMessage.value = message;
  toastVisible.value = true;
};

const reports = ref([]);
const isLoading = ref(false);
let interval = null;

const fetchInitialReports = async () => {
  isLoading.value = true;
  try {
    const res = await axiosClient.get("/api/reports");
    reports.value = res.data.data;
  } catch (e) {
    console.error("Ошибка при загрузке жалоб:", e);
  } finally {
    isLoading.value = false;
  }
};

const fetchNewReports = async () => {
  if (reports.value.length === 0) return;

  console.log("Fetch interval triggered");

  const latestId = reports.value[0]?.id ?? 0;

  try {
    const res = await axiosClient.get("/api/reports", {
      params: { after: latestId },
    });

    const newReports = res.data.data;
    if (newReports.length) {
      reports.value = [...newReports, ...reports.value];
    }
  } catch (e) {
    console.error("Ошибка при обновлении жалоб:", e);
  }
};

onMounted(() => {
  fetchInitialReports();
  interval = setInterval(fetchNewReports, 10000);
});

onBeforeUnmount(() => {
  clearInterval(interval);
});

const typeLabel = {
  post: "Пост",
  comment: "Комментарий",
  profile: "Профиль",
  group: "Группа",
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
</script>

<template>
  <div class="flex flex-col gap-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Жалобы</h1>
      <p class="text-sm text-gray-500">
        Все поступившие жалобы от пользователей
      </p>
    </div>

    <div
      class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                ID
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Тип
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                На кого
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Объект
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Категория
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Комментарий
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Кем отправлено
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                Действие
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="isLoading">
              <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                Загрузка...
              </td>
            </tr>
            <tr v-else-if="reports.length === 0">
              <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                Жалоб пока нет
              </td>
            </tr>
            <tr
              v-for="report in reports"
              :key="report.id"
              class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">{{ report.id }}</td>
              <td class="px-6 py-4 text-sm text-gray-700">
                {{ typeLabel[report.type] || report.type }}
              </td>

              <td class="px-6 py-4 text-sm text-blue-600">
                <RouterLink
                  :to="
                    report.target_user?.profile?.slug
                      ? `/profile/${report.target_user.profile.slug}`
                      : `/users/${report.target_user_id}`
                  "
                  class="hover:underline">
                  {{
                    report.target_user?.name ||
                    `Пользователь #${report.target_user_id}`
                  }}
                </RouterLink>
              </td>

              <td class="px-6 py-4 text-sm text-blue-600">
                <!-- Пост -->
                <RouterLink
                  v-if="report.target_type === 'App\\Models\\Post'"
                  :to="`/posts/${report.target?.slug}`"
                  class="hover:underline">
                  {{
                    report.target?.title
                      ? `Пост - ${report.target.title}`
                      : `Пост #${report.target_id}`
                  }}
                </RouterLink>

                <!-- Комментарий -->
                <RouterLink
                  v-else-if="report.target_type === 'App\\Models\\Comment'"
                  :to="`/comments/${report.target_id}`"
                  class="hover:underline">
                  Комментарий - #{{ report.target_id }}
                </RouterLink>

                <!-- Профиль -->
                <RouterLink
                  v-else-if="report.target_type === 'App\\Models\\User'"
                  :to="`/users/${
                    report.target?.profile?.slug || report.target_id
                  }`"
                  class="hover:underline">
                  {{
                    report.target?.name
                      ? `Профиль - ${report.target.name}`
                      : `Профиль #${report.target_id}`
                  }}
                </RouterLink>

                <!-- Группа -->
                <RouterLink
                  v-else-if="report.target_type === 'App\\Models\\Group'"
                  :to="`/groups/${report.target?.slug || report.target_id}`"
                  class="hover:underline">
                  {{
                    report.target?.name
                      ? `Группа - ${report.target.name}`
                      : `Группа #${report.target_id}`
                  }}
                </RouterLink>

                <!-- Неизвестно -->
                <span v-else>ID #{{ report.target_id }}</span>
              </td>

              <td class="px-6 py-4 text-sm text-gray-700">
                {{ categoryLabel[report.category] || report.category }}
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ report.comment || "—" }}
              </td>

              <td class="px-6 py-4 text-sm text-blue-600">
                <RouterLink
                  :to="
                    report.user?.profile?.slug
                      ? `/profile/${report.user.profile.slug}`
                      : `/users/${report.user_id}`
                  "
                  class="hover:underline">
                  {{ report.user?.name || `Пользователь #${report.user_id}` }}
                </RouterLink>
              </td>

              <td class="px-6 py-4 text-right">
                <RouterLink
                  :to="{ name: 'AdminReportShow', params: { id: report.id } }"
                  class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-all duration-200 text-sm font-medium">
                  Перейти
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <Toast :message="toastMessage" v-model:show="toastVisible" />
</template>
