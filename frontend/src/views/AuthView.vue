<script setup>
import { computed, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { apiError } from "../api/http";
const props = defineProps({ mode: { type: String, required: true } });
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const isLogin = computed(() => props.mode === "login");
const form = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});
const error = ref("");
const showPassword = ref(false);
async function submit() {
  error.value = "";
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    error.value = "Masukkan alamat email yang valid.";
    return;
  }
  if (form.value.password.length < 8) {
    error.value = "Kata sandi harus terdiri dari minimal 8 karakter.";
    return;
  }
  if (
    !isLogin.value &&
    form.value.password !== form.value.password_confirmation
  ) {
    error.value = "Konfirmasi kata sandi belum sama.";
    return;
  }
  try {
    if (isLogin.value)
      await auth.login({
        email: form.value.email,
        password: form.value.password,
      });
    else await auth.register(form.value);
    if (!auth.authenticated) {
      router.push("/login");
      window.dispatchEvent(
        new CustomEvent("cerailegal:toast", {
          detail: "Akun berhasil dibuat. Silakan masuk.",
        }),
      );
      return;
    }
    router.push(String(route.query.redirect || "/dashboard"));
  } catch (e) {
    error.value = apiError(e);
  }
}
</script>
<template>
  <section class="page-shell auth-shell">
    <div class="auth-aside">
      <div class="eyebrow">RUANG YANG AMAN UNTUK MEMULAI</div>
      <h1>
        {{
          isLogin
            ? "Selamat datang kembali."
            : "Mulai dengan satu langkah kecil."
        }}
      </h1>
      <p>Kelola pengajuan dan dokumenmu dalam satu tempat yang aman.</p>
      <div class="auth-aside-mark">⚖</div>
      <div class="auth-aside-bottom">
        Informasi jelas. Langkah yang lebih tenang.
      </div>
    </div>
    <div class="auth-main">
      <div class="auth-card">
        <div class="eyebrow">
          {{ isLogin ? "MASUK KE AKUN" : "BUAT AKUN BARU" }}
        </div>
        <h2>
          {{ isLogin ? "Lanjutkan perjalananmu." : "Buat akun CeraiLegal." }}
        </h2>
        <p>
          {{
            isLogin
              ? "Masuk untuk melihat pengajuan dan dokumenmu."
              : "Daftar untuk mengelola pengajuan secara privat."
          }}
        </p>
        <form @submit.prevent="submit" class="field-stack">
          <label v-if="!isLogin" class="field-label"
            >Nama lengkap<input
              v-model.trim="form.name"
              class="input-control"
              autocomplete="name"
              required
              placeholder="Nama sesuai identitas" /></label
          ><label class="field-label"
            >Email<input
              v-model.trim="form.email"
              class="input-control"
              type="email"
              autocomplete="email"
              required
              placeholder="nama@email.com" /></label
          ><label class="field-label"
            >Kata sandi<span class="password-wrap"
              ><input
                v-model="form.password"
                class="input-control"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                placeholder="Minimal 8 karakter" /><button
                type="button"
                class="password-toggle"
                :aria-label="
                  showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'
                "
                :title="
                  showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'
                "
                @click="showPassword = !showPassword"
              >
                <svg v-if="showPassword" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M3 12s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7Z" />
                  <circle cx="12" cy="12" r="2.5" />
                </svg>
                <svg v-else viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8" />
                  <path
                    d="M9.9 5.2A10.8 10.8 0 0 1 12 5c5.5 0 9 7 9 7a15 15 0 0 1-3.1 3.8M6.2 6.2C4.1 7.7 3 12 3 12s3.5 7 9 7a10 10 0 0 0 3.1-.5"
                  />
                </svg></button></span></label
          ><label v-if="!isLogin" class="field-label"
            >Ulangi kata sandi<input
              v-model="form.password_confirmation"
              class="input-control"
              type="password"
              autocomplete="new-password"
              required
              placeholder="Masukkan ulang kata sandi"
          /></label>
          <div v-if="error" class="inline-error" role="alert">{{ error }}</div>
          <button class="btn btn-primary btn-block" :disabled="auth.loading">
            {{ auth.loading ? "Memproses…" : isLogin ? "Masuk" : "Buat akun" }}
            <span></span>
          </button>
        </form>
        <div class="auth-switch">
          {{ isLogin ? "Belum punya akun?" : "Sudah punya akun?" }}
          <RouterLink :to="isLogin ? '/register' : '/login'">{{
            isLogin ? "Daftar" : "Masuk"
          }}</RouterLink>
        </div>
        <div class="auth-privacy">
          Data dan dokumenmu dilindungi.
          <RouterLink to="/privacy">Pelajari privasi</RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>
