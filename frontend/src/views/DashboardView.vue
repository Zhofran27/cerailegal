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
const statusLabel = (status) =>
  ({
    submitted: "Diajukan",
    draft: "Draf",
    in_review: "Dalam pemeriksaan",
    in_court: "Dalam persidangan",
    completed: "Selesai",
    approved: "Selesai",
    rejected: "Perlu tindak lanjut",
  })[status] ||
  status ||
  "Status diperbarui";
onMounted(async () => {
  try {
    const data = await api.cases();
    cases.value = Array.isArray(data) ? data : data?.data || [];
  } catch (e) {
    error.value = apiError(e);
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
            Halo{{
              auth.user?.name ? `, ${auth.user.name.split(" ")[0]}` : ""
            }}.
          </h1>
          <p class="page-intro">
            Pantau pengajuan dan kelola dokumenmu di sini.
          </p>
        </div>
        <RouterLink to="/cases/create" class="btn btn-primary"
          >Buat pengajuan <span></span
        ></RouterLink>
      </div>
      <div class="dashboard-notice">
        <span>✳</span>
        <div>
          <b>Perjalananmu tetap di tanganmu.</b>
          <p>
            Kamu dapat memeriksa status dan dokumen perkara kapan saja melalui
            akun ini.
          </p>
        </div>
      </div>
      <div class="list-heading">
        <div>
          <div class="eyebrow">PENGAJUANMU</div>
          <h2>Daftar perkara</h2>
        </div>
        <span>{{ cases.length }} perkara</span>
      </div>
      <div v-if="busy" class="empty-panel">Memuat daftar perkara…</div>
      <div v-else-if="error" class="empty-panel">
        <p>{{ error }}</p>
        <button class="text-link" @click="$router.go(0)">Coba lagi</button>
      </div>
      <div v-else-if="!cases.length" class="empty-panel">
        <div class="empty-illustration">⌂</div>
        <h3>Belum ada pengajuan</h3>
        <p>
          Ketika kamu siap, mulai pengajuan baru. Kami akan memandumu tahap demi
          tahap.
        </p>
        <RouterLink to="/cases/create" class="btn btn-primary"
          >Mulai pengajuan <span></span
        ></RouterLink>
      </div>
      <div v-else class="case-list">
        <RouterLink
          v-for="item in cases"
          :key="item.id"
          :to="`/cases/${item.id}`"
          class="case-row"
          ><div class="case-stamp">CL</div>
          <div class="case-main">
            <span>{{ item.case_number || `Perkara #${item.id}` }}</span>
            <h3>{{ item.court?.name || "Pengadilan belum dipilih" }}</h3>
            <p>
              Dibuat
              {{
                new Date(item.created_at).toLocaleDateString("id-ID", {
                  day: "numeric",
                  month: "long",
                  year: "numeric",
                })
              }}
            </p>
          </div>
          <span
            :class="[
              'status-pill',
              item.status === 'completed' ? 'status-done' : '',
            ]"
            >{{ statusLabel(item.status) }}</span
          ><span class="row-arrow"></span
        ></RouterLink>
      </div>
      <div class="dashboard-help">
        <span>Perlu informasi lebih lanjut?</span
        ><RouterLink to="/faqs" class="text-link"
          >Lihat pusat informasi
        </RouterLink>
      </div>
    </div>
  </section>
</template>
