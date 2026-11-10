<script setup>
import { onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import { api } from "../api";
import { apiError } from "../api/http";

const cases = ref([]);
const busy = ref(true);
const error = ref("");
const searchInput = ref("");
const search = ref("");
const status = ref("");
const page = ref(1);
const lastPage = ref(1);
const summary = ref({ total: 0, needs_review: 0, in_progress: 0, completed: 0 });

const statusLabels = {
  draft: "Draf",
  submitted: "Pengajuan diterima",
  diajukan: "Pengajuan diterima",
  in_review: "Verifikasi berkas",
  diproses: "Verifikasi berkas",
  registered: "Nomor perkara terbit",
  summons: "Pemanggilan",
  mediation: "Mediasi",
  in_court: "Persidangan",
  sedang_sidang: "Persidangan",
  completed: "Putusan selesai",
  approved: "Putusan selesai",
  selesai: "Putusan selesai",
  rejected: "Perlu perbaikan berkas",
  ditolak: "Perlu perbaikan berkas",
};

const filters = [
  { value: "", label: "Semua perkara" },
  { value: "submitted", label: "Pengajuan diterima" },
  { value: "in_review", label: "Verifikasi berkas" },
  { value: "registered", label: "Nomor perkara terbit" },
  { value: "summons", label: "Pemanggilan" },
  { value: "mediation", label: "Mediasi" },
  { value: "in_court", label: "Persidangan" },
  { value: "completed", label: "Selesai" },
  { value: "rejected", label: "Perlu tindak lanjut" },
];

const moneyDate = (value) =>
  new Date(value).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });

async function loadCases() {
  busy.value = true;
  error.value = "";
  try {
    const result = await api.adminCases({
      search: search.value || undefined,
      status: status.value || undefined,
      page: page.value,
      per_page: 12,
    });
    cases.value = result.data || [];
    page.value = result.meta?.current_page || 1;
    lastPage.value = result.meta?.last_page || 1;
    summary.value = result.summary || summary.value;
  } catch (requestError) {
    error.value = apiError(requestError);
  } finally {
    busy.value = false;
  }
}

function searchCases() {
  search.value = searchInput.value.trim();
  page.value = 1;
  loadCases();
}

function changeStatus() {
  page.value = 1;
  loadCases();
}

function movePage(nextPage) {
  page.value = nextPage;
  loadCases();
}

onMounted(loadCases);
</script>

<template>
  <section class="page-shell admin-page">
    <div class="container admin-container">
      <header class="admin-page-heading">
        <div>
          <span class="admin-eyebrow">PANEL ADMIN</span>
          <h1>Kelola perkara</h1>
          <p>Periksa berkas, catat tindak lanjut, dan perbarui tahap perkara.</p>
        </div>
      </header>

      <div class="admin-summary-grid">
        <article class="admin-summary-card">
          <span>Total perkara</span>
          <strong>{{ summary.total }}</strong>
        </article>
        <article class="admin-summary-card">
          <span>Perlu ditinjau</span>
          <strong>{{ summary.needs_review }}</strong>
        </article>
        <article class="admin-summary-card">
          <span>Sedang diproses</span>
          <strong>{{ summary.in_progress }}</strong>
        </article>
        <article class="admin-summary-card">
          <span>Selesai</span>
          <strong>{{ summary.completed }}</strong>
        </article>
      </div>

      <div class="admin-list-section">
      <div class="admin-list-toolbar">
        <form class="admin-search" @submit.prevent="searchCases">
          <label class="sr-only" for="admin-case-search">Cari perkara</label>
          <input
            id="admin-case-search"
            v-model="searchInput"
            class="input-control"
            placeholder="Cari nomor perkara atau nama pemohon"
          />
          <button class="btn btn-primary" type="submit">Cari</button>
        </form>
        <label class="admin-filter-label">
          <span class="sr-only">Filter status perkara</span>
          <select
            v-model="status"
            class="input-control"
            @change="changeStatus"
          >
            <option
              v-for="option in filters"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </label>
      </div>

      <div v-if="busy" class="admin-state-panel">Memuat daftar perkara...</div>
      <div v-else-if="error" class="admin-state-panel admin-error-state">
        <p>{{ error }}</p>
        <button class="btn btn-outline" @click="loadCases">Coba lagi</button>
      </div>
      <div v-else-if="!cases.length" class="admin-state-panel">
        Belum ada perkara yang cocok dengan pencarian ini.
      </div>

      <div v-else class="admin-case-list">
        <article
          v-for="item in cases"
          :key="item.id"
          class="admin-case-row"
        >
          <div class="admin-case-identity">
            <span class="admin-case-number">
              {{ item.case_number || `Perkara #${item.id}` }}
            </span>
            <h2>{{ item.applicant_detail?.full_name || item.user?.name || "Pemohon" }}</h2>
            <p>{{ item.court?.name || "Pengadilan belum dipilih" }}</p>
          </div>
          <div class="admin-case-meta">
            <span :class="['status-pill', { 'admin-status-done': ['completed', 'approved', 'selesai'].includes(item.status) }]">
              {{ statusLabels[item.status] || item.status }}
            </span>
            <small>Diterima {{ moneyDate(item.created_at) }}</small>
          </div>
          <RouterLink
            :to="`/admin/cases/${item.id}`"
            class="btn btn-outline admin-open-case"
          >
            Tinjau perkara
          </RouterLink>
        </article>
      </div>

      <nav v-if="!busy && lastPage > 1" class="admin-pagination" aria-label="Halaman daftar perkara">
        <button
          class="btn btn-outline"
          :disabled="page <= 1"
          @click="movePage(page - 1)"
        >
          Sebelumnya
        </button>
        <span>Halaman {{ page }} dari {{ lastPage }}</span>
        <button
          class="btn btn-outline"
          :disabled="page >= lastPage"
          @click="movePage(page + 1)"
        >
          Berikutnya
        </button>
      </nav>
      </div>
    </div>
  </section>
</template>
