// store/report.js
import { defineStore } from "pinia";
import { ref } from "vue";
import axiosClient from "../axios";

export const useReportStore = defineStore("report", () => {
  const count = ref(0);

  const fetchCount = async () => {
    try {
      const response = await axiosClient.get("/api/admin/reports/count");
      count.value = response.data.count;
    } catch (error) {
      console.error("Ошибка при получении количества жалоб", error);
    }
  };

  let interval = null;
  if (!interval) {
    fetchCount();
    interval = setInterval(fetchCount, 10000);
  }

  return {
    count,
    fetchCount,
  };
});
