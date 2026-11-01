import { defineStore } from "pinia";
import { api } from "../api";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    token: localStorage.getItem("cerailegal_token"),
    user: null,
    loading: false,
  }),
  getters: { authenticated: (state) => Boolean(state.token) },
  actions: {
    async login(credentials) {
      this.loading = true;
      try {
        const data = await api.login(credentials);
        this.token = data.access_token || data.token;
        localStorage.setItem("cerailegal_token", this.token);
        this.user = data.user || null;
        if (!this.user) await this.fetchMe();
      } finally {
        this.loading = false;
      }
    },
    async register(data) {
      this.loading = true;
      try {
        const result = await api.register(data);
        if (result.access_token || result.token) {
          this.token = result.access_token || result.token;
          localStorage.setItem("cerailegal_token", this.token);
          this.user = result.user || null;
        }
      } finally {
        this.loading = false;
      }
    },
    async fetchMe() {
      const profile = await api.me();
      this.user = profile.user || profile;
      return this.user;
    },
    async logout() {
      try {
        if (this.token) await api.logout();
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem("cerailegal_token");
      }
    },
  },
});
