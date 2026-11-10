<script setup>
import { computed, onMounted, ref } from "vue";
import { api } from "../api";
import { apiError } from "../api/http";

const stats = ref(null);
const loading = ref(true);
const error = ref("");
const number = (value) => new Intl.NumberFormat("id-ID").format(value || 0);
const statusItems = computed(() => {
  const summary = stats.value?.summary || {};
  return [
    {
      key: "diajukan",
      label: "Diajukan",
      value: summary.diajukan || 0,
      color: "teal",
    },
    {
      key: "diproses",
      label: "Sedang diproses",
      value: summary.diproses || 0,
      color: "teal",
    },
    {
      key: "selesai",
      label: "Selesai",
      value: summary.selesai || 0,
      color: "teal",
    },
    { key: "draft", label: "Draf", value: summary.draft || 0, color: "muted" },
    {
      key: "ditolak",
      label: "Perlu tindak lanjut",
      value: summary.ditolak || 0,
      color: "amber",
    },
  ];
});
const maxStatus = computed(() =>
  Math.max(1, ...statusItems.value.map((item) => item.value)),
);
const inProgress = computed(() => stats.value?.summary?.diproses || 0);
const completed = computed(() => stats.value?.summary?.selesai || 0);
onMounted(async () => {
  try {
    stats.value = await api.statistics();
  } catch (e) {
    error.value = apiError(e);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <section class="transparency-page">
    <div class="transparency-container">
      <div class="services-heading transparency-heading">
        <div>
          <span class="services-eyebrow">KETERBUKAAN INFORMASI</span>
          <h1>Transparansi</h1>
          <p class="services-intro">
            Data agregat kinerja penanganan perkara administratif.
          </p>
        </div>
        <span class="data-source">Diperbarui dari sistem</span>
      </div>

      <div v-if="error" class="inline-error" role="alert">
        {{ error }}
        <button class="text-link" @click="$router.go(0)">Coba lagi</button>
      </div>
      <div class="transparency-metrics">
        <article class="transparency-metric">
          <div class="metric-caption">Beban Berjalan <span>▱</span></div>
          <strong>{{ loading ? "—" : number(inProgress) }}</strong>
          <p>Perkara dalam proses</p>
          <small>Data status perkara terkini</small>
        </article>
        <article class="transparency-metric">
          <div class="metric-caption">Perkara Selesai</div>
          <strong>{{ loading ? "—" : number(completed) }}</strong>
          <p>Perkara yang telah selesai</p>
          <small>Termasuk status putusan</small>
        </article>
        <article class="transparency-metric">
          <div class="metric-caption">Akses Layanan</div>
          <strong>{{ loading ? "—" : number(stats?.total_courts) }}</strong>
          <p>Pengadilan terdaftar</p>
          <small>{{
            loading
              ? "Memuat data"
              : `${number(stats?.total_faqs)} informasi tersedia`
          }}</small>
        </article>
      </div>

      <div v-if="loading" class="transparency-loading">
        Memuat data transparansi…
      </div>
      <div v-else-if="stats" class="transparency-panels">
        <section class="transparency-panel monthly-panel">
          <div class="transparency-panel-heading">
            <h2>Perkara menurut status</h2>
            <span>JUMLAH PERKARA</span>
          </div>
          <div
            class="status-chart"
            role="img"
            aria-label="Grafik jumlah perkara berdasarkan status"
          >
            <div
              v-for="item in statusItems"
              :key="item.key"
              class="status-chart-column"
            >
              <span class="chart-value">{{ number(item.value) }}</span>
              <div class="chart-bar-wrap">
                <div
                  :class="['chart-bar', `bar-${item.color}`]"
                  :style="{
                    height: `${Math.max(5, (item.value / maxStatus) * 100)}%`,
                  }"
                ></div>
              </div>
              <span class="chart-label">{{ item.label }}</span>
            </div>
          </div>
          <div class="chart-legend">
            <span><i></i> Jumlah tercatat per status</span
            ><b>{{ number(stats.total_cases) }} total perkara</b>
          </div>
        </section>

        <section class="transparency-panel process-panel">
          <div class="transparency-panel-heading">
            <h2>Ringkasan perkara</h2>
            <span>DATA AGREGAT</span>
          </div>
          <div class="progress-status-list">
            <div
              v-for="item in statusItems"
              :key="item.key"
              class="progress-status"
            >
              <div class="progress-status-label">
                <span>{{ item.label }}</span
                ><b>{{ number(item.value) }}</b>
              </div>
              <div class="progress-track">
                <i
                  :class="`progress-${item.color}`"
                  :style="{
                    width: `${Math.max(item.value ? 2 : 0, (item.value / maxStatus) * 100)}%`,
                  }"
                ></i>
              </div>
            </div>
          </div>
          <div class="panel-footnote">
            <span>Status dapat berubah setelah pembaruan perkara</span
            ><b>Data diperbarui berkala</b>
          </div>
        </section>
      </div>
      <div v-else class="transparency-loading">Data belum tersedia.</div>
      <p class="transparency-note">
        Data ditampilkan dalam bentuk agregat. Informasi ini bukan nasihat hukum
        resmi pengadilan.
      </p>
    </div>
  </section>
</template>
