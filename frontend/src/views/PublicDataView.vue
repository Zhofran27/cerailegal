<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { api } from "../api";
import { apiError } from "../api/http";
import { useAuthStore } from "../stores/auth";
const props = defineProps({ page: { type: String, required: true } });
const router = useRouter();
const auth = useAuthStore();
const title = computed(
  () =>
    ({
      faqs: "Informasi, dengan bahasa yang mudah dipahami.",
      courts: "Temukan pengadilan yang tepat.",
      status: "Periksa perkembangan perkara.",
      cost: "Rencanakan taksiran panjar biaya.",
    })[props.page],
);
const eyebrow = computed(
  () =>
    ({
      faqs: "GLOSARIUM & FAQ",
      courts: "DIREKTORI PENGADILAN",
      status: "CEK STATUS",
      cost: "SIMULASI BIAYA",
    })[props.page],
);
const search = ref("");
const category = ref("");
const city = ref("");
const rows = ref([]);
const courts = ref([]);
const busy = ref(false);
const error = ref("");
const selectedCourt = ref("");
const radius = ref("1");
const estimate = ref(null);
const trackingId = ref("");
const loginRequired = ref(false);
let debounce;
async function load() {
  if (!["faqs", "courts"].includes(props.page)) return;
  busy.value = true;
  error.value = "";
  try {
    const params =
      props.page === "faqs"
        ? { search: search.value, category: category.value || undefined }
        : { search: search.value, city: city.value || undefined };
    const data =
      props.page === "faqs" ? await api.faqs(params) : await api.courts(params);
    rows.value = Array.isArray(data) ? data : data?.data || [];
  } catch (e) {
    error.value = apiError(e);
  } finally {
    busy.value = false;
  }
}
async function loadCourts() {
  try {
    const data = await api.courts();
    courts.value = Array.isArray(data) ? data : data?.data || [];
  } catch (e) {
    error.value = apiError(e);
  }
}
async function calculate() {
  error.value = "";
  estimate.value = null;
  if (!selectedCourt.value) {
    error.value = "Pilih pengadilan untuk menghitung taksiran.";
    return;
  }
  busy.value = true;
  try {
    estimate.value = await api.estimate({
      court_id: selectedCourt.value,
      radius: radius.value,
    });
  } catch (e) {
    error.value = apiError(e);
  } finally {
    busy.value = false;
  }
}
async function track() {
  error.value = "";
  loginRequired.value = false;
  if (!trackingId.value.trim()) {
    error.value = "Masukkan nomor perkara terlebih dahulu.";
    return;
  }
  if (!auth.authenticated) {
    loginRequired.value = true;
    error.value = "Masuk ke akun terlebih dahulu untuk melanjutkan pencarian perkara.";
    return;
  }
  busy.value = true;
  try {
    const data = await api.cases();
    const list = Array.isArray(data) ? data : data?.data || [];
    const target = list.find(
      (item) =>
        String(item.id) === trackingId.value.trim() ||
        item.case_number?.toLowerCase() ===
          trackingId.value.trim().toLowerCase(),
    );
    if (!target) {
      error.value =
        "Perkara tidak ditemukan di akun ini. Periksa nomor perkara dan coba lagi.";
      return;
    }
    router.push(`/cases/${target.id}`);
  } catch (e) {
    error.value = apiError(e);
  } finally {
    busy.value = false;
  }
}
onMounted(() => {
  load();
  if (props.page === "cost") loadCourts();
});
watch([search, category, city], () => {
  clearTimeout(debounce);
  debounce = setTimeout(load, 300);
});
const rupiah = (v) =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(v || 0);
</script>

<template>
  <section class="page-shell">
    <div class="container content-container">
      <header class="services-heading shared-page-heading">
        <span class="services-eyebrow">{{ eyebrow }}</span>
        <h1>{{ title }}</h1>
        <p class="services-intro" v-if="page === 'faqs'">
          Jawaban ringkas untuk memahami istilah dan proses perkara.
        </p>
        <p class="services-intro" v-else-if="page === 'courts'">
          Cari berdasarkan nama pengadilan atau kota.
        </p>
        <p class="services-intro" v-else-if="page === 'status' && !auth.authenticated">
          Masuk ke akun untuk melihat informasi dan riwayat perkara secara aman.
        </p>
        <p class="services-intro" v-else-if="page === 'status'">
          Masukkan nomor perkara untuk membuka informasi dan riwayatnya.
        </p>
        <p class="services-intro" v-else>
          Nilai ini adalah simulasi. Besaran resmi ditetapkan oleh pengadilan
          terkait.
        </p>
      </header>
      <div v-if="page === 'faqs' || page === 'courts'" class="data-panel">
        <div class="filter-row">
          <label class="search-field"
            ><input
              v-model="search"
              :placeholder="
                page === 'faqs'
                  ? 'Cari pertanyaan atau istilah'
                  : 'Cari nama pengadilan atau kota'
              " /></label
          ><input
            v-if="page === 'courts'"
            v-model="city"
            class="input-control city-filter"
            placeholder="Kota"
          /><select
            v-if="page === 'faqs'"
            v-model="category"
            class="input-control"
          >
            <option value="">Semua kategori</option>
            <option
              v-for="c in [
                ...new Set(rows.map((r) => r.category).filter(Boolean)),
              ]"
              :key="c"
              :value="c"
            >
              {{ c }}
            </option>
          </select>
        </div>
        <div v-if="busy" class="empty-state">Memuat informasi…</div>
        <div v-else-if="error" class="inline-error">
          {{ error }} <button class="text-link" @click="load">Coba lagi</button>
        </div>
        <div v-else-if="!rows.length" class="empty-state">
          Belum ada data yang cocok dengan pencarianmu.
        </div>
        <div v-else-if="page === 'faqs'" class="faq-list">
          <details
            v-for="(faq, index) in rows"
            :key="faq.id || index"
            class="faq-item"
          >
            <summary>
              <span>{{ faq.category || "Informasi" }}</span
              ><b>{{ faq.question }}</b
              ><i>＋</i>
            </summary>
            <p>{{ faq.answer }}</p>
          </details>
        </div>
        <div v-else class="court-list">
          <article
            v-for="(court, index) in rows"
            :key="court.id || index"
            class="court-item"
          >
            <div>
              <span>{{ court.type || "PENGADILAN" }}</span>
              <h3>{{ court.name }}</h3>
              <p>
                {{ court.city
                }}<template v-if="court.address">
                  · {{ court.address }}</template
                >
              </p>
            </div>
            <a
              v-if="court.website"
              :href="court.website"
              target="_blank"
              rel="noreferrer"
              class="text-link"
              >Kunjungi
            </a>
          </article>
        </div>
      </div>
      <div v-else-if="page === 'status'" class="lookup-card">
        <h2>Masukkan nomor perkara</h2>
        <p v-if="!auth.authenticated">
          Untuk melindungi privasimu, detail perkara hanya dapat dilihat setelah
          masuk ke akun.
        </p>
        <p v-else>Masukkan nomor perkara yang terhubung dengan akunmu.</p>
        <form @submit.prevent="track">
          <label class="field-label"
            >Nomor perkara<input
              v-model="trackingId"
              @input="loginRequired = false; error = ''"
              class="input-control"
              placeholder="Contoh: CL-2026-ABCDE"
          /></label>
          <div v-if="error" class="inline-error">{{ error }}</div>
          <RouterLink
            v-if="loginRequired"
            :to="{ path: '/login', query: { redirect: '/status' } }"
            class="text-link status-login-feedback"
          >
            Masuk ke akun
          </RouterLink>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit">Lanjutkan</button>
          </div>
        </form>
      </div>
      <div v-else class="estimate-card">
        <div class="estimate-fields">
          <label class="field-label"
            >Pengadilan<select v-model="selectedCourt" class="input-control">
              <option value="">Pilih pengadilan</option>
              <option v-for="court in courts" :key="court.id" :value="court.id">
                {{ court.name }} — {{ court.city }}
              </option>
            </select></label
          ><label class="field-label"
            >Radius panggilan<select v-model="radius" class="input-control">
              <option value="1">Radius 1</option>
              <option value="2">Radius 2</option>
              <option value="3">Radius 3</option>
              <option value="4">Radius 4</option>
              <option value="5">Radius 5</option>
            </select></label
          >
        </div>
        <div v-if="error" class="inline-error">{{ error }}</div>
        <button class="btn btn-primary" :disabled="busy" @click="calculate">
          {{ busy ? "Menghitung…" : "Hitung estimasi" }} <span></span>
        </button>
        <div v-if="estimate" class="estimate-result">
          <div class="eyebrow">TAKSIRAN PANJAR</div>
          <h2>
            {{
              estimate.total_estimate_formatted ||
              rupiah(estimate.total_estimate)
            }}
          </h2>
          <p>
            {{ estimate.court?.name }} · Zona radius {{ estimate.radius_zone }}
          </p>
          <div v-if="estimate.breakdown" class="breakdown">
            <div v-for="(value, key) in estimate.breakdown" :key="key">
              <span>{{ key.replaceAll("_", " ") }}</span
              ><b>{{
                rupiah(typeof value === "object" ? value.subtotal : value)
              }}</b>
            </div>
          </div>
          <small>{{ estimate.notes }}</small>
        </div>
        <p class="disclaimer">
          Taksiran dapat berubah mengikuti ketentuan dan SK panjar biaya pada
          pengadilan terkait.
        </p>
      </div>
    </div>
  </section>
</template>
