<script setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRoute } from "vue-router";
import { api } from "../api";
import { apiError } from "../api/http";

const route = useRoute();
const item = ref(null);
const busy = ref(true);
const saving = ref(false);
const error = ref("");
const notice = ref("");
const form = ref({ status: "in_review", case_number: "", notes: "" });

const stages = [
  { value: "submitted", label: "Pengajuan diterima" },
  { value: "in_review", label: "Verifikasi berkas" },
  { value: "registered", label: "Nomor perkara terbit" },
  { value: "summons", label: "Pemanggilan" },
  { value: "mediation", label: "Mediasi" },
  { value: "in_court", label: "Persidangan" },
  { value: "completed", label: "Putusan selesai" },
  { value: "rejected", label: "Perlu perbaikan berkas" },
];
const labels = {
  ...Object.fromEntries(stages.map(({ value, label }) => [value, label])),
  draft: "Draf",
  diajukan: "Pengajuan diterima",
  diproses: "Verifikasi berkas",
  sedang_sidang: "Persidangan",
  approved: "Putusan selesai",
  selesai: "Putusan selesai",
  ditolak: "Perlu perbaikan berkas",
};
const documentsByType = computed(() => {
  const labelsByType = {
    ktp: "KTP pemohon",
    buku_nikah: "Buku nikah atau akta perkawinan",
    kartu_keluarga: "Kartu keluarga",
    bukti_lainnya: "Bukti pendukung lainnya",
  };
  return (item.value?.documents || []).map((doc) => ({
    ...doc,
    label: labelsByType[doc.type] || doc.type,
  }));
});
const history = computed(() =>
  [...(item.value?.status_logs || [])].sort(
    (a, b) => new Date(b.changed_at) - new Date(a.changed_at),
  ),
);

async function loadCase() {
  busy.value = true;
  error.value = "";
  try {
    item.value = await api.adminCase(route.params.id);
    form.value.status = item.value.status || "in_review";
    form.value.case_number = item.value.case_number || "";
  } catch (e) {
    error.value = apiError(e);
  } finally {
    busy.value = false;
  }
}

async function saveUpdate() {
  saving.value = true;
  error.value = "";
  notice.value = "";
  try {
    item.value = await api.updateAdminCase(route.params.id, {
      status: form.value.status,
      case_number: form.value.case_number.trim() || null,
      notes: form.value.notes.trim() || null,
    });
    form.value.notes = "";
    notice.value = "Tahapan perkara berhasil diperbarui dan masuk ke riwayat.";
  } catch (e) {
    error.value = apiError(e);
  } finally {
    saving.value = false;
  }
}

async function downloadDocument(doc) {
  error.value = "";
  try {
    const response = await api.adminDocument(doc.id);
    const url = URL.createObjectURL(response.data);
    const link = document.createElement("a");
    link.href = url;
    link.download = doc.original_name || "dokumen-perkara";
    document.body.append(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
  } catch (e) {
    error.value = apiError(e);
  }
}

function formatDate(value) {
  if (!value) return "";
  return new Date(value).toLocaleString("id-ID", {
    dateStyle: "long",
    timeStyle: "short",
  });
}

onMounted(loadCase);
</script>

<template>
  <section class="page-shell admin-page">
    <div class="container admin-container">
      <RouterLink to="/admin/cases" class="back-link">Daftar perkara</RouterLink>

      <div v-if="busy" class="admin-state-panel">Memuat detail perkara...</div>
      <div v-else-if="error && !item" class="admin-state-panel admin-error-state">
        <p>{{ error }}</p>
        <button class="btn btn-outline" @click="loadCase">Coba lagi</button>
      </div>

      <template v-else-if="item">
        <header class="admin-detail-heading">
          <div>
            <span class="admin-eyebrow">PEMERIKSAAN PERKARA</span>
            <h1>{{ item.case_number || `Perkara #${item.id}` }}</h1>
            <p>{{ item.court?.name || "Pengadilan belum dipilih" }}<template v-if="item.court?.city">, {{ item.court.city }}</template></p>
          </div>
          <span class="status-pill">{{ labels[item.status] || item.status }}</span>
        </header>

        <div v-if="error" class="admin-feedback admin-feedback-error" role="alert">{{ error }}</div>
        <div v-if="notice" class="admin-feedback" role="status">{{ notice }}</div>

        <div class="admin-detail-grid">
          <div class="admin-detail-main">
            <section class="detail-panel admin-info-panel">
              <div class="panel-heading"><span class="admin-eyebrow">DATA PENGAJUAN</span></div>
              <div class="admin-fact-grid">
                <div><small>Nama pemohon</small><b>{{ item.applicant_detail?.full_name || item.user?.name || "Belum diisi" }}</b></div>
                <div><small>Email akun</small><b>{{ item.user?.email || "Tidak tersedia" }}</b></div>
                <div><small>NIK pemohon</small><b>{{ item.applicant_detail?.nik || "Belum diisi" }}</b></div>
                <div><small>Alamat pemohon</small><b>{{ item.applicant_detail?.address || "Belum diisi" }}</b></div>
                <div><small>Nama pasangan</small><b>{{ item.spouse_detail?.full_name || "Belum diisi" }}</b></div>
                <div><small>Tanggal perkawinan</small><b>{{ item.marriage_detail?.marriage_date || "Belum diisi" }}</b></div>
                <div><small>Nomor akta/buku nikah</small><b>{{ item.marriage_detail?.marriage_certificate_number || "Belum dicantumkan" }}</b></div>
                <div><small>Tempat pencatatan</small><b>{{ item.marriage_detail?.kua_or_civil_registry || "Belum dicantumkan" }}</b></div>
              </div>
              <div class="admin-reason"><small>Alasan pengajuan</small><p>{{ item.reason || "Belum diisi" }}</p></div>
            </section>

            <section class="detail-panel admin-documents-panel">
              <div class="panel-heading">
                <div><span class="admin-eyebrow">PEMERIKSAAN BERKAS</span><h2>Dokumen pemohon</h2></div>
                <span class="optional">PDF, JPG, PNG</span>
              </div>
              <div v-if="!documentsByType.length" class="admin-empty-inline">Belum ada dokumen yang diunggah.</div>
              <div v-else class="admin-document-list">
                <article v-for="doc in documentsByType" :key="doc.id" class="admin-document-row">
                  <div><b>{{ doc.label }}</b><span>{{ doc.original_name }}</span></div>
                  <button class="btn btn-outline" type="button" @click="downloadDocument(doc)">Unduh berkas</button>
                </article>
              </div>
            </section>

            <section class="detail-panel admin-history-panel">
              <div class="panel-heading"><div><span class="admin-eyebrow">CATATAN PENANGANAN</span><h2>Riwayat tahapan</h2></div></div>
              <ol v-if="history.length" class="admin-history-list">
                <li v-for="log in history" :key="log.id">
                  <span class="admin-history-dot"></span>
                  <div><b>{{ labels[log.status] || log.status }}</b><p v-if="log.notes">{{ log.notes }}</p><time>{{ formatDate(log.changed_at) }}</time></div>
                </li>
              </ol>
              <p v-else class="admin-empty-inline">Belum ada catatan perubahan status.</p>
            </section>
          </div>

          <aside class="detail-panel admin-action-panel">
            <span class="admin-eyebrow">TINDAKAN ADMIN</span>
            <h2>Perbarui penanganan</h2>
            <p class="admin-action-help">Pilih tahap terbaru dan tulis catatan agar pemohon mengetahui perkembangan perkaranya.</p>
            <form class="admin-update-form" @submit.prevent="saveUpdate">
              <label class="field-label" for="case-status">Tahap perkara</label>
              <select id="case-status" v-model="form.status" class="input-control">
                <option v-for="stage in stages" :key="stage.value" :value="stage.value">{{ stage.label }}</option>
              </select>

              <label class="field-label" for="case-number">Nomor perkara resmi</label>
              <input id="case-number" v-model="form.case_number" class="input-control" maxlength="100" placeholder="Isi saat nomor resmi terbit" />
              <small class="admin-field-help">Nomor perkara boleh dikosongkan sampai tersedia.</small>

              <label class="field-label" for="case-notes">Catatan untuk pemohon</label>
              <textarea id="case-notes" v-model="form.notes" class="input-control" rows="5" maxlength="1000" placeholder="Contoh: Berkas sudah diperiksa. Mohon unggah ulang halaman buku nikah yang lebih jelas."></textarea>
              <small class="admin-field-help">Catatan tersimpan di riwayat dan ditampilkan kepada pemohon.</small>

              <button class="btn btn-primary admin-save-button" type="submit" :disabled="saving">
                {{ saving ? "Menyimpan..." : "Simpan pembaruan" }}
              </button>
            </form>
          </aside>
        </div>
      </template>
    </div>
  </section>
</template>
