<script setup>
import { ref } from "vue";
import { RouterLink } from "vue-router";

const isSpinning = ref(false);
const toggleSpin = () => {
  isSpinning.value = !isSpinning.value;
};
</script>

<template>
  <div
    class="relative flex items-center justify-center min-h-screen bg-black text-white overflow-hidden">
    <img
      src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fphotobooth.cdn.sports.ru%2Fpreset%2Fmessage%2Fe%2Ffa%2Fdfb7a7a914c6a876e64dc3c910850.jpeg&f=1&nofb=1&ipt=69049827a7a73d6fe62eb07694795ab526bec318601dce441996567b8a425105"
      alt="окак"
      :class="[
        'absolute inset-0 w-full h-full object-cover opacity-40 transition-transform duration-1000',
        { 'scale-110': isSpinning },
      ]" />

    <div class="z-10 text-center flex flex-col gap-6 items-center">
      <div>
        <h1
          @click="toggleSpin"
          class="text-6xl font-extrabold uppercase drop-shadow-lg hover:text-yellow-400 cursor-pointer transition-all duration-300">
          Окак
        </h1>
      </div>

      <div class="flex flex-col">
        <p class="text-3xl mt-4 drop-shadow">Ошибка 404</p>
        <p class="text-xl text-gray-400 mt-2 drop-shadow">
          Страница не найдена
        </p>
      </div>

      <RouterLink
        to="/"
        class="mt-8 px-6 py-3 bg-yellow-500 text-black font-bold rounded-full hover:bg-yellow-400 transition-all flex items-center gap-2 animate-bounce">
        <span>Вернуться на главную</span>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor">
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z"
            clip-rule="evenodd" />
        </svg>
      </RouterLink>
    </div>
  </div>
</template>
