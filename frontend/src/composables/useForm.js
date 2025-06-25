// composables/useForm.js
import { ref, watch, nextTick } from "vue";
import axiosClient from "../axios";

export function useForm(config) {
  const { endpoint, defaultData, successMessage } = config;
  const data = ref({ ...defaultData });
  const errors = ref(
    Object.fromEntries(Object.keys(defaultData).map((k) => [k, ""]))
  );
  const isLoading = ref(false);

  const formData = new FormData();
  for (const key in data.value) {
    if (key === "images") {
      data.value.images.forEach((image) => {
        formData.append("images[]", image);
      });
    } else {
      formData.append(key, data.value[key]);
    }
  }

  const resetForm = () => {
    data.value = { ...defaultData };
    for (const key in errors.value) {
      errors.value[key] = "";
    }
  };

  const submit = async (onSuccess) => {
    isLoading.value = true;
    try {
      await axiosClient.post(endpoint, data.value);
      resetForm();
      onSuccess(successMessage);
    } catch (error) {
      if (error.response?.data?.errors) {
        for (const [field, message] of Object.entries(
          error.response.data.errors
        )) {
          errors.value[field] = Array.isArray(message) ? message[0] : message;
        }
      } else {
        alert(error.response?.data?.message || "Произошла ошибка");
      }
    } finally {
      isLoading.value = false;
    }
  };

  return {
    data,
    errors,
    isLoading,
    resetForm,
    submit,
  };
}
