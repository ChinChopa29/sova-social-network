<script setup></script>

<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
      Добро пожаловать в админ-панель
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-blue-50 p-5 rounded-lg border border-blue-100">
        <h3 class="text-lg font-semibold text-blue-800">Пользователи</h3>
        <p class="text-3xl font-bold mt-2 text-blue-600">1,248</p>
      </div>
      <div class="bg-green-50 p-5 rounded-lg border border-green-100">
        <h3 class="text-lg font-semibold text-green-800">Активность</h3>
        <p class="text-3xl font-bold mt-2 text-green-600">86%</p>
      </div>
      <div class="bg-purple-50 p-5 rounded-lg border border-purple-100">
        <h3 class="text-lg font-semibold text-purple-800">Задачи</h3>
        <p class="text-3xl font-bold mt-2 text-purple-600">24</p>
      </div>
    </div>

    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">
        Последние действия
      </h3>
      <div class="space-y-3">
        <div
          class="flex items-center justify-between p-3 hover:bg-gray-100 rounded transition-colors">
          <div class="flex items-center space-x-3">
            <div
              class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 text-blue-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <span>Новый пользователь зарегистрирован</span>
          </div>
          <span class="text-sm text-gray-500">2 мин назад</span>
        </div>
        <div
          class="flex items-center justify-between p-3 hover:bg-gray-100 rounded transition-colors">
          <div class="flex items-center space-x-3">
            <div
              class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 text-green-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span>Обновлены настройки системы</span>
          </div>
          <span class="text-sm text-gray-500">15 мин назад</span>
        </div>
      </div>
    </div>
  </div>
</template>
