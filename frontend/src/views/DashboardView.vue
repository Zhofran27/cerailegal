<script setup>
import { onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import { api } from "../api";
import { apiError } from "../api/http";
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
const cases = ref([]);
const busy = ref(true);
const error = ref("");

const progressStages = [
  "Pengajuan diterima",
  "Verifikasi berkas",
  "Nomor perkara terbit",
  "Pemanggilan",
  "Mediasi",
  "Persidangan",
  "Putusan selesai",
  "Perlu perbaikan berkas",
];

const statusLabels = {
  submitted: "Pengajuan diterima",
  draft: "Draf",
  in_review: "Verifikasi berkas",
  registered: "Nomor perkara terbit",
  summons: "Pemanggilan",
  mediation: "Mediasi",
  in_court: "Persidangan",
  completed: "Putusan selesai",
  approved: "Putusan selesai",
  rejected: "Perlu perbaikan berkas",
  diajukan: "Pengajuan diterima",
  diproses: "Verifikasi berkas",
  sedang_sidang: "Persidangan",
  selesai: "Putusan selesai",
  ditolak: "Perlu perbaikan berkas",
};

const statusLabel = (status) =>
  statusLabels[status] || status || "Status diperbarui";

const progressFor = (status) => {
  const normalized = String(status || "").toLowerCase();

  if (["rejected", "ditolak"].includes(normalized)) return 8;
  if (["completed", "approved", "selesai"].includes(normalized)) return 7;
  if (["in_court", "sedang_sidang"].includes(normalized)) return 6;
  if (normalized === "mediation") return 5;
  if (normalized === "summons") return 4;
  if (normalized === "registered") return 3;
  if (["in_review", "diproses"].includes(normalized)) return 2;
  if (["submitted", "diajukan"].includes(normalized)) return 1;

  return 0;
};

const dateLabel = (value) =>
  new Date(value).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });

onMounted(async () => {
  try {
    const data = await api.cases();
    cases.value = Array.isArray(data) ? data : data?.data || [];
  } catch (requestError) {
    error.value = apiError(requestError);
  } finally {
    busy.value = false;
  }
});
</script>

<template>
  <section class="page-shell">
    <div class="container dashboard-container">
      <div class="dashboard-heading">
        <div>
          <div class="eyebrow">RUANG PRIBADIMU</div>
          <h1 class="page-title">
            Halo{{ auth.user?.name ? `, ${auth.user.name.split(" ")[0]}` : "" }}.
          </h1>
          <p class="page-intro">
            Pantau pengajuan dan kelola dokumenmu di sini.
          </p>
        </div>
        <RouterLink to="/cases/create" class="btn btn-primary">
          Buat pengajuan
        </RouterLink>
      </div>

      <div class="list-heading">
        <div>
          <h2>Daftar perkara</h2>
        </div>
        <span>{{ cases.length }} perkara</span>
      </div>

      <div v-if="busy" class="empty-panel">Memuat daftar perkara...</div>
      <div v-else-if="error" class="empty-panel">
        <p>{{ error }}</p>
        <button class="text-link" @click="$router.go(0)">Coba lagi</button>
      </div>
      <div v-else-if="!cases.length" class="empty-panel dashboard-empty">
        <h3>Belum ada pengajuan</h3>
        <p>
          Ketika kamu siap, mulai pengajuan baru. Kami akan memandumu tahap demi
          tahap.
        </p>
        <RouterLink to="/cases/create" class="btn btn-primary">
          Mulai pengajuan
        </RouterLink>
      </div>

      <div v-else class="dashboard-case-list">
        <article
          v-for="item in cases"
          :key="item.id"
          class="dashboard-case-card"
        >
          <div class="dashboard-case-head">
            <div>
              <span class="case-reference-label">NOMOR PENGAJUAN</span>
              <h3>{{ item.case_number || `Perkara #${item.id}` }}</h3>
              <p>{{ item.court?.name || "Pengadilan belum dipilih" }}</p>
            </div>
            <span
              :class="[
                'status-pill',
                ['completed', 'approved', 'selesai'].includes(item.status)
                  ? 'status-done'
                  : '',
              ]"
            >
              {{ statusLabel(item.status) }}
            </span>
          </div>

          <div class="dashboard-progress-heading">
            <span>Progres perkara</span>
            <b>{{ progressFor(item.status) }} dari {{ progressStages.length }} tahap</b>
          </div>
          <div
            class="dashboard-progress-track"
            :aria-label="'Progres ' + progressFor(item.status) + ' dari ' + progressStages.length + ' tahap'"
          >
            <span
              v-for="(stage, index) in progressStages"
              :key="stage"
              :class="{ reached: index < progressFor(item.status) }"
            ></span>
          </div>
          <div class="dashboard-progress-labels">
            <span
              v-for="(stage, index) in progressStages"
              :key="stage"
              :class="{ reached: index < progressFor(item.status) }"
            >
              {{ stage }}
            </span>
          </div>

          <div class="dashboard-case-latest">
            <div>
              <b>{{ item.status_logs?.[item.status_logs.length - 1]?.notes || "Pengajuan telah diterima." }}</b>
              <p>Diajukan {{ dateLabel(item.created_at) }}</p>
            </div>
            <RouterLink :to="`/cases/${item.id}`" class="btn btn-outline">
              Lihat perkara
            </RouterLink>
          </div>
        </article>
      </div>

      <div class="dashboard-help">
        <span>Perlu informasi lebih lanjut?</span>
        <RouterLink to="/faqs" class="text-link">
          Buka pusat informasi
        </RouterLink>
      </div>
    </div>
  </section>
</template>
