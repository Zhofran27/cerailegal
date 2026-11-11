import axios from "axios";

let baseURL = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api";
baseURL = baseURL.replace(/\/+$/, "");
if (!baseURL.endsWith("/api")) {
  baseURL += "/api";
}

const http = axios.create({
  baseURL,
  headers: { Accept: "application/json" },
});

http.interceptors.request.use((config) => {
  const token = localStorage.getItem("cerailegal_token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

http.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("cerailegal_token");
      if (
        window.location.pathname.startsWith("/dashboard") ||
        window.location.pathname.startsWith("/cases") ||
        window.location.pathname.startsWith("/admin")
      ) {
        window.location.assign("/login");
      }
    }
    return Promise.reject(error);
  },
);

export const payload = (response) => response.data?.data ?? response.data;
export const apiError = (error) =>
  error.response?.data?.message ||
  (error.response?.data?.errors &&
    Object.values(error.response.data.errors).flat()[0]) ||
  "Terjadi kendala. Silakan coba kembali.";
export default http;
