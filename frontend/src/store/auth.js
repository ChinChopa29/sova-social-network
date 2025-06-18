import { defineStore } from "pinia";
import axiosClient from "../axios";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
  }),
  actions: {
    async getUser() {
      const { data } = await axiosClient.get("/api/user");
      this.user = data;
    },
    async logout() {
      await axiosClient.post("/logout");
      this.user = null;
    },
  },
});
