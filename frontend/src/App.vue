<script setup>
import { onMounted, ref } from "vue";
import { RouterLink, RouterView, useRoute, useRouter } from "vue-router";
import { useAuthStore } from "./stores/auth";

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const menuOpen = ref(false);
const toast = ref("");
let toastTimer;
const links = [
  { label: "Beranda", to: "/" },
  { label: "Layanan", to: "/services" },
  { label: "Alur Tahapan", to: "/workflow" },
  { label: "Transparansi", to: "/transparency" },
  { label: "Cek Status", to: "/status" },
];
onMounted(async () => {
  if (auth.authenticated && !auth.user) {
    try {
      await auth.fetchMe();
    } catch {}
  }
});
window.addEventListener("cerailegal:toast", (event) => {
  toast.value = event.detail;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    toast.value = "";
  }, 3600);
});
async function signOut() {
  await auth.logout();
  router.push("/");
}
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <header class="site-header">
      <div class="nav-wrap">
        <RouterLink to="/" class="brand" aria-label="CeraiLegal beranda"
          ><span class="brand-mark">⚖</span> CeraiLegal</RouterLink
        >
        <button
          class="mobile-toggle"
          aria-label="Buka navigasi"
          @click="menuOpen = !menuOpen"
        >
          ☰
        </button>
        <nav :class="['main-nav', { 'nav-open': menuOpen }]">
          <RouterLink
            v-for="link in links"
            :key="link.label"
            :to="link.to"
            @click="menuOpen = false"
            >{{ link.label }}</RouterLink
          >
          <RouterLink to="/faqs" @click="menuOpen = false">FAQ</RouterLink>
        </nav>
        <div class="nav-actions">
          <RouterLink
            v-if="!auth.authenticated"
            class="btn btn-primary nav-extra"
            to="/login"
            >Masuk</RouterLink
          >
          <RouterLink
            v-if="!auth.authenticated"
            class="btn btn-outline nav-extra"
            to="/register"
            >Daftar</RouterLink
          >
          <template v-else>
            <button class="btn btn-outline nav-extra" @click="signOut">
              Keluar
            </button>
            <RouterLink
              class="profile-link"
              to="/dashboard"
              aria-label="Profil dan dashboard"
            >
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8" r="3.2" />
                <path d="M5.5 20c.5-3.6 2.8-5.5 6.5-5.5s6 1.9 6.5 5.5" />
              </svg>
            </RouterLink>
          </template>
        </div>
      </div>
    </header>
    <main class="flex-1"><RouterView /></main>
    <footer class="site-footer">
      <div class="footer-inner">
        <div>
          <RouterLink to="/" class="brand"
            ><span class="brand-mark">⚖</span> CeraiLegal</RouterLink
          >
          <p>Pendamping perkara dalam bahasa sehari-hari.</p>
        </div>
        <div class="footer-links">
          <RouterLink to="/workflow">Alur Tahapan</RouterLink
          ><RouterLink to="/courts">Cari pengadilan</RouterLink
          ><RouterLink to="/faqs">Glosarium & FAQ</RouterLink
          ><RouterLink to="/privacy">Privasi</RouterLink
          ><RouterLink to="/mediation">Mediasi</RouterLink>
        </div>
        <div class="footer-note">
          Informasi di platform ini bersifat edukatif dan bukan pengganti
          nasihat hukum profesional.
        </div>
      </div>
      <div class="footer-bottom">
        © {{ new Date().getFullYear() }} CeraiLegal
        <span>Informasi yang jelas. Langkah yang lebih tenang.</span>
      </div>
    </footer>
    <Transition name="toast"
      ><div v-if="toast" class="toast-message" role="status">
        {{ toast }}
      </div></Transition
    >
  </div>
</template>
