<script setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRoute } from "vue-router";
import { api } from "../api";
import { apiError } from "../api/http";
const route = useRoute();
const item = ref(null);
const busy = ref(true);
const error = ref("");
const uploading = ref(false);
const labels = {
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
const latestLog = computed(() =>
  [...(item.value?.status_logs || [])].sort(
    (first, second) =>
      new Date(second.changed_at).getTime() -
      new Date(first.changed_at).getTime(),
  )[0],
);
const activeStage = computed(() => progressStages[currentProgress.value - 1] || "Menunggu verifikasi");
const currentProgress = computed(() => {
  const status = String(item.value?.status || "").toLowerCase();
  if (["rejected", "ditolak"].includes(status)) return 8;
  if (["completed", "approved", "selesai"].includes(status)) return 7;
  if (["in_court", "sedang_sidang"].includes(status)) return 6;
  if (status === "mediation") return 5;
  if (status === "summons") return 4;
  if (status === "registered") return 3;
  if (["in_review", "diproses"].includes(status)) return 2;
  if (["submitted", "diajukan"].includes(status)) return 1;
  return 0;
});
const documentsLocked = computed(() =>
  String(item.value?.status || "").toLowerCase() !== "draft",
);
const canSubmitDraft = computed(() =>
  ["ktp", "buku_nikah"].every((type) =>
    item.value?.documents?.some((document) => document.type === type),
  ),
);
const submittingDraft = ref(false);
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
  if (documentsLocked.value) {
    error.value = "Pengajuan sudah dikirim. Dokumen tidak dapat diubah lagi.";
    event.target.value = "";
    return;
  }
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
async function submitDraft() {
  submittingDraft.value = true;
  error.value = "";
  try {
    item.value = await api.submitCase(item.value.id);
    window.dispatchEvent(
      new CustomEvent("cerailegal:toast", {
        detail: "Pengajuan berhasil dikirim. Data dan dokumen kini terkunci.",
      }),
    );
  } catch (e) {
    error.value = apiError(e);
  } finally {
    submittingDraft.value = false;
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
              <div class="eyebrow">PROGRES PERKARA</div>
              <span class="detail-progress-count">
                {{ currentProgress }} dari {{ progressStages.length }} tahap
              </span>
            </div>
            <ol class="case-stage-list" :aria-label="`Progres sampai tahap ${activeStage}`">
              <li
                v-for="(stage, index) in progressStages"
                :key="stage"
                :class="{ reached: index < currentProgress - 1, current: index === currentProgress - 1 }"
              >
                <span class="case-stage-marker">{{ index + 1 }}</span>
                <b>{{ stage }}</b>
              </li>
            </ol>
            <div class="case-stage-current">
              <b>{{ activeStage }}</b>
              <span>{{ labels[item.status] || item.status }}</span>
            </div>
            <p v-if="latestLog?.notes" class="case-stage-note">{{ latestLog.notes }}</p>
            <time v-if="latestLog?.changed_at" class="case-stage-time" :datetime="latestLog.changed_at">
              {{ new Date(latestLog.changed_at).toLocaleString("id-ID") }}
            </time>
          </section>
          <section class="detail-panel">
            <div class="panel-heading">
              <div class="eyebrow">RINGKASAN PENGAJUAN</div>
            </div>
            <h3>Data pemohon</h3>
            <p class="detail-text">
              {{ item.applicant_detail?.full_name || item.user?.name || "Belum dilengkapi" }}
            </p>
            <p v-if="item.applicant_detail?.nik" class="detail-muted">
              NIK {{ item.applicant_detail.nik }}
            </p>
            <p v-if="item.applicant_detail?.address" class="detail-muted">
              {{ item.applicant_detail.address }}
            </p>
            <hr />
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
          <p v-if="documentsLocked" class="documents-lock-note">
            Data dan berkas dikunci sejak pengajuan dikirim agar tidak berubah selama proses pemeriksaan. Hubungi admin jika ada perbaikan.
          </p>
          <div class="document-list">
            <article
              v-for="docType in docs"
              :key="docType.type"
              class="document-row"
            >
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
              <label v-if="!documentsLocked" class="btn btn-outline upload-btn"
                >{{ uploading ? "Mengunggah…" : "Unggah"
                }}<input
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png"
                  :disabled="uploading"
                  @change="upload(docType.type, $event)"
              /></label>
            </article>
          </div></section
        ><section v-if="item.status === 'draft'" class="draft-submit-panel">
          <p v-if="!canSubmitDraft" class="case-helper-text">
            Unggah KTP dan Buku Nikah atau Akta Perkawinan sebelum mengirim pengajuan.
          </p>
          <button
            class="btn btn-primary"
            :disabled="!canSubmitDraft || uploading || submittingDraft"
            @click="submitDraft"
          >
            {{ submittingDraft ? "Mengirim..." : "Kirim pengajuan" }}
          </button>
        </section
      ></template>
    </div>
  </section>
</template>
