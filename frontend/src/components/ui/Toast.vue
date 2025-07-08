<script setup>
import { watch } from "vue";

const props = defineProps({
  message: String,
  show: Boolean,
});
const emit = defineEmits(["update:show"]);

watch(
  () => props.show,
  (value) => {
    if (value) {
      setTimeout(() => {
        emit("update:show", false);
      }, 3000);
    }
  }
);
</script>

<template>
  <transition name="fade">
    <div
      v-if="show"
      class="fixed top-5 right-5 z-50 bg-green-500 text-white px-4 py-2 rounded shadow-lg">
      {{ message }}
    </div>
  </transition>
</template>
