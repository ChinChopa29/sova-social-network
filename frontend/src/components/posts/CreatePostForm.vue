<script setup>
import { ref, watch } from "vue";
import axiosClient from "@/../axios";

const props = defineProps({
  visible: Boolean,
  userId: Number,
});

const emit = defineEmits(["close", "postCreated"]);

const form = ref({
  title: "",
  body: "",
});

function close() {
  emit("close");
}

async function submit() {
  try {
    const { data } = await axiosClient.post(`/api/posts`, {
      ...form.value,
      user_id: props.userId,
    });

    emit("postCreated", data);
    close();
    form.value.title = "";
    form.value.body = "";
  } catch (e) {
    console.error("Ошибка при создании поста", e);
  }
}
</script>

<template>
  <div
    v-if="visible"
    class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md relative">
      <button
        @click="close"
        class="absolute top-2 right-2 text-gray-500 hover:text-black">
        ✖
      </button>
      <h2 class="text-xl font-semibold mb-4">Создать пост</h2>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-sm font-medium">Заголовок</label>
          <input
            v-model="form.title"
            class="w-full border p-2 rounded mt-1"
            required />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium">Содержимое</label>
          <textarea
            v-model="form.body"
            class="w-full border p-2 rounded mt-1"
            rows="4"
            required />
        </div>

        <button
          type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
          Опубликовать
        </button>
      </form>
    </div>
  </div>
</template>
