<script setup>
import { onMounted, ref } from "vue";
import { RouterLink, useRoute } from "vue-router";
import { api } from "../api";
import { apiError } from "../api/http";
const route = useRoute();
const item = ref(null);
const busy = ref(true);
const error = ref("");
const uploading = ref(false);
const labels = {
  submitted: "Diajukan",
  draft: "Draf",
  in_review: "Dalam pemeriksaan",
  in_court: "Dalam persidangan",
  completed: "Selesai",
  approved: "Selesai",
  rejected: "Perlu tindak lanjut",
};
const docs = [
  { type: "ktp", label: "Kartu Tanda Penduduk" },
  { type: "buku_nikah", label: "Buku Nikah / Akta Perkawinan" },
  { type: "kartu_keluarga", label: "Kartu Keluarga" },
  { type: "bukti_lainnya", label: "Bukti pendukung lainnya" },
];
async function load() {
  busy.value = true;
  error.value = "";
  try {
    item.value = await api.case(route.params.id);
  } catch (e) {
    error.value = apiError(e);
  } finally {
    busy.value = false;
  }
}
async function upload(type, event) {
  const file = event.target.files?.[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) {
    error.value = "Ukuran file maksimal 5 MB.";
    event.target.value = "";
    return;
  }
  if (!["application/pdf", "image/jpeg", "image/png"].includes(file.type)) {
    error.value = "Unggah file PDF, JPG, atau PNG.";
    event.target.value = "";
    return;
  }
  uploading.value = true;
  error.value = "";
  try {
    await api.uploadDocument(item.value.id, file, type);
    await load();
    window.dispatchEvent(
      new CustomEvent("cerailegal:toast", {
        detail: "Dokumen berhasil diunggah.",
      }),
    );
  } catch (e) {
    error.value = apiError(e);
  } finally {
    uploading.value = false;
    event.target.value = "";
  }
}
async function download(doc) {
  try {
    const response = await api.downloadDocument(doc.id);
    const url = URL.createObjectURL(response.data);
    const link = document.createElement("a");
    link.href = url;
    link.download = doc.original_name || "dokumen";
    link.click();
    URL.revokeObjectURL(url);
  } catch (e) {
    error.value = apiError(e);
  }
}
onMounted(load);
</script>
<template>
  <section class="page-shell">
    <div class="container content-container">
      <RouterLink to="/dashboard" class="back-link"> Dashboard</RouterLink>
      <div v-if="busy" class="empty-panel">Memuat detail perkara…</div>
      <div v-else-if="error && !item" class="empty-panel">
        <p>{{ error }}</p>
        <button class="text-link" @click="load">Coba lagi</button>
      </div>
      <template v-else-if="item"
        ><div class="detail-heading">
          <div>
            <div class="eyebrow">DETAIL PERKARA</div>
            <h1 class="page-title">
              {{ item.case_number || `Perkara #${item.id}` }}
            </h1>
            <p class="page-intro">
              {{ item.court?.name
              }}<template v-if="item.court?.city">
                · {{ item.court.city }}</template
              >
            </p>
          </div>
          <span class="status-pill">{{
            labels[item.status] || item.status
          }}</span>
        </div>
        <div v-if="error" class="inline-error">{{ error }}</div>
        <div class="detail-grid">
          <section class="detail-panel">
            <div class="panel-heading">
              <div class="eyebrow">RIWAYAT STATUS</div>
            </div>
            <div class="audit-list">
              <article
                v-for="(log, index) in [...(item.status_logs || [])].reverse()"
                :key="log.id"
                class="audit-item"
              >
                <div :class="['audit-dot', { current: index === 0 }]" />
                <div>
                  <b>{{ labels[log.status] || log.status }}</b>
                  <p>{{ log.notes }}</p>
                  <small>{{
                    new Date(log.changed_at).toLocaleString("id-ID")
                  }}</small>
                </div>
              </article>
              <div v-if="!item.status_logs?.length" class="empty-state">
                Belum ada pembaruan status.
              </div>
            </div>
          </section>
          <section class="detail-panel">
            <div class="panel-heading">
              <div class="eyebrow">RINGKASAN PENGAJUAN</div>
            </div>
            <h3>Alasan pengajuan</h3>
            <p class="detail-text">{{ item.reason }}</p>
            <hr />
            <h3>Informasi pasangan</h3>
            <p class="detail-text">
              {{ item.spouse_detail?.full_name || "Belum dilengkapi" }}
            </p>
            <p v-if="item.spouse_detail?.address" class="detail-muted">
              {{ item.spouse_detail.address }}
            </p>
            <hr />
            <h3>Informasi perkawinan</h3>
            <p class="detail-text">
              {{
                item.marriage_detail?.marriage_date ||
                "Tanggal belum dicantumkan"
              }}
            </p>
            <p
              v-if="item.marriage_detail?.kua_or_civil_registry"
              class="detail-muted"
            >
              {{ item.marriage_detail.kua_or_civil_registry }}
            </p>
          </section>
        </div>
        <section class="detail-panel documents-panel">
          <div class="panel-heading">
            <div>
              <div class="eyebrow">DOKUMEN PERKARA</div>
              <h2>Dokumen pendukung</h2>
            </div>
            <span class="optional">PDF, JPG, PNG · maks. 5 MB</span>
          </div>
          <div class="document-list">
            <article
              v-for="docType in docs"
              :key="docType.type"
              class="document-row"
            >
              <div class="doc-symbol">▤</div>
              <div>
                <b>{{ docType.label }}</b>
                <p
                  v-for="doc in item.documents?.filter(
                    (d) => d.type === docType.type,
                  )"
                  :key="doc.id"
                >
                  {{ doc.original_name }}
                  <button class="text-link" @click="download(doc)">
                    Unduh
                  </button>
                </p>
                <small
                  v-if="!item.documents?.some((d) => d.type === docType.type)"
                  >Belum ada dokumen</small
                >
              </div>
              <label class="btn btn-outline upload-btn"
                >{{ uploading ? "Mengunggah…" : "Unggah"
                }}<input
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png"
                  :disabled="uploading"
                  @change="upload(docType.type, $event)"
              /></label>
            </article>
          </div></section
      ></template>
    </div>
  </section>
</template>
