<script setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { api } from "../api";
import { apiError } from "../api/http";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
const auth = useAuthStore();
const step = ref(-1);
const courts = ref([]);
const busy = ref(false);
const error = ref("");
const estimate = ref(null);
const selectedFiles = ref({
  ktp: null,
  buku_nikah: null,
  kartu_keluarga: null,
  bukti_lainnya: null,
});

const steps = [
  {
    title: "Data kamu",
    description: "Pastikan data sesuai dengan identitas resmi.",
  },
  {
    title: "Data pasangan",
    description: "Isi informasi pasangan untuk kebutuhan administrasi perkara.",
  },
  {
    title: "Pernikahan",
    description: "Lengkapi informasi sesuai Buku Nikah atau Akta Perkawinan.",
  },
  {
    title: "Alasan perceraian",
    description:
      "Pilih alasan yang paling sesuai dan ceritakan situasinya bila berkenan.",
  },
  {
    title: "Kelengkapan berkas",
    description:
      "Siapkan dokumen dalam format PDF, JPG, atau PNG. Maksimal 5 MB per berkas.",
  },
  {
    title: "Pengadilan & biaya",
    description:
      "Pilih pengadilan tujuan untuk melihat perkiraan panjar perkara.",
  },
];

const reasons = [
  "Pertengkaran terus-menerus",
  "Salah satu pihak meninggalkan pihak lain",
  "Kekerasan dalam rumah tangga",
  "Pelanggaran kewajiban dalam perkawinan",
  "Alasan lainnya",
];

const documents = [
  { type: "ktp", title: "KTP elektronik pemohon", note: "Wajib", required: true },
  {
    type: "buku_nikah",
    title: "Buku Nikah atau Akta Perkawinan",
    note: "Wajib",
    required: true,
  },
  {
    type: "kartu_keluarga",
    title: "Kartu Keluarga terbaru",
    note: "Disarankan",
    required: false,
  },
  {
    type: "bukti_lainnya",
    title: "Bukti pendukung lainnya",
    note: "Opsional",
    required: false,
  },
];

const form = ref({
  court_id: "",
  radius: 1,
  reason_category: "",
  reason_detail: "",
  applicant_detail: {
    full_name: auth.user?.name || "",
    nik: "",
    birth_date: "",
    address: "",
  },
  spouse_detail: {
    full_name: "",
    nik: "",
    phone: "",
    address: "",
    occupation: "",
  },
  marriage_detail: {
    marriage_date: "",
    marriage_certificate_number: "",
    kua_or_civil_registry: "",
    has_children: false,
    children_count: 0,
    last_address: "",
  },
});

const currentStep = computed(() => steps[step.value]);
const filesReady = computed(
  () => Object.values(selectedFiles.value).filter(Boolean).length,
);
const selectedCourt = computed(() =>
  courts.value.find((court) => String(court.id) === String(form.value.court_id)),
);
const fullReason = computed(() =>
  [form.value.reason_category, form.value.reason_detail.trim()]
    .filter(Boolean)
    .join("\n\n"),
);
const currency = (amount) =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(amount || 0);

onMounted(async () => {
  if (!auth.user) {
    try {
      await auth.fetchMe();
    } catch {}
  }

  if (!form.value.applicant_detail.full_name) {
    form.value.applicant_detail.full_name = auth.user?.name || "";
  }

  try {
    const data = await api.courts();
    courts.value = Array.isArray(data) ? data : data?.data || [];
  } catch (requestError) {
    error.value = apiError(requestError);
  }
});

function chooseFile(type, event) {
  const file = event.target.files?.[0];
  if (!file) return;

  if (file.size > 5 * 1024 * 1024) {
    error.value = "Ukuran setiap berkas maksimal 5 MB.";
    event.target.value = "";
    return;
  }

  if (!["application/pdf", "image/jpeg", "image/png"].includes(file.type)) {
    error.value = "Pilih berkas PDF, JPG, atau PNG.";
    event.target.value = "";
    return;
  }

  selectedFiles.value[type] = file;
  error.value = "";
}

function validateStep() {
  if (step.value === 0) {
    const person = form.value.applicant_detail;
    const hasApplicantDetails =
      person.full_name.trim() &&
      /^\d{16}$/.test(person.nik) &&
      person.birth_date &&
      person.address.trim();

    if (!hasApplicantDetails) {
      error.value =
        "Lengkapi nama, NIK 16 digit, tanggal lahir, dan alamat domisili.";
      return false;
    }
  }

  if (step.value === 1 && !form.value.spouse_detail.full_name.trim()) {
    error.value = "Nama lengkap pasangan wajib diisi.";
    return false;
  }

  if (step.value === 2 && !form.value.marriage_detail.marriage_date) {
    error.value = "Tanggal pernikahan wajib diisi.";
    return false;
  }

  if (step.value === 3 && !form.value.reason_category) {
    error.value = "Pilih alasan pengajuan terlebih dahulu.";
    return false;
  }

  if (
    step.value === 4 &&
    (!selectedFiles.value.ktp || !selectedFiles.value.buku_nikah)
  ) {
    error.value =
      "Unggah KTP pemohon dan Buku Nikah atau Akta Perkawinan untuk melanjutkan.";
    return false;
  }

  if (step.value === 5 && !form.value.court_id) {
    error.value = "Pilih pengadilan tujuan terlebih dahulu.";
    return false;
  }

  return true;
}

async function next() {
  error.value = "";
  if (!validateStep()) return;

  if (step.value === 5) {
    try {
      estimate.value = await api.estimate({
        court_id: form.value.court_id,
        radius: form.value.radius,
      });
    } catch (requestError) {
      estimate.value = null;
      error.value = apiError(requestError);
      return;
    }
  }

  if (step.value < steps.length) step.value++;
}

function editStep(index) {
  error.value = "";
  step.value = index;
}

async function submit() {
  error.value = "";
  busy.value = true;
  let createdCase;

  try {
    createdCase = await api.createCase({
      court_id: Number(form.value.court_id),
      reason: fullReason.value,
      status: "draft",
      applicant_detail: form.value.applicant_detail,
      spouse_detail: form.value.spouse_detail,
      marriage_detail: form.value.marriage_detail,
    });

    const uploads = Object.entries(selectedFiles.value).filter(([, file]) => file);
    for (const [type, file] of uploads) {
      await api.uploadDocument(createdCase.id, file, type);
    }

    await api.submitCase(createdCase.id);

    window.dispatchEvent(
      new CustomEvent("cerailegal:toast", {
        detail: "Pengajuan dan dokumen berhasil dikirim.",
      }),
    );
    router.push(`/cases/${createdCase.id}`);
  } catch (requestError) {
    if (createdCase?.id) {
      window.dispatchEvent(
        new CustomEvent("cerailegal:toast", {
          detail:
            "Draf tersimpan. Beberapa dokumen belum terunggah. Lengkapi dokumennya di halaman perkara sebelum mengirim.",
        }),
      );
      router.push(`/cases/${createdCase.id}`);
    } else {
      error.value = apiError(requestError);
    }
  } finally {
    busy.value = false;
  }
}
</script>

<template>
  <section class="page-shell case-flow-page">
    <div v-if="step === -1" class="container case-start-wrap">
      <RouterLink to="/dashboard" class="back-link">
        Kembali ke dashboard
      </RouterLink>

      <section class="case-start-card">
        <div class="eyebrow">LANGKAH AWAL PENGAJUAN</div>
        <h1>Ajukan perceraian</h1>
        <p class="case-start-intro">
          Layanan mandiri untuk membantu menyiapkan pengajuan perkara secara
          bertahap.
        </p>

        <div class="case-checklist-heading">
          <h2>Yang perlu disiapkan</h2>
          <span>4 dokumen dasar</span>
        </div>
        <ul class="case-checklist">
          <li v-for="document in documents" :key="document.type">
            <span>{{ document.title }}</span>
            <small :class="{ required: document.required }">
              {{ document.note }}
            </small>
          </li>
        </ul>

        <button
          class="btn btn-primary btn-block btn-large"
          @click="step = 0"
        >
          Mulai pengajuan
        </button>
        <p class="case-privacy-note">
          Dokumen hanya dapat diakses melalui akunmu.
        </p>
      </section>
    </div>

    <div v-else class="container case-flow-container">
      <RouterLink to="/dashboard" class="back-link">
        Kembali ke dashboard
      </RouterLink>

      <div class="case-flow-layout">
        <aside class="case-step-panel" aria-label="Tahapan pengajuan">
          <div class="case-step-panel-heading">
            <span>TAHAPAN</span>
            <b>{{ Math.min(step + 1, steps.length) }} / {{ steps.length }}</b>
          </div>
          <ol class="case-step-list">
            <li
              v-for="(item, index) in steps"
              :key="item.title"
              :class="{ active: step === index, complete: step > index }"
            >
              <button
                type="button"
                :disabled="index >= step"
                @click="editStep(index)"
              >
                <span class="case-step-number">{{ index + 1 }}</span>
                <span>
                  {{ item.title }}
                  <small v-if="step === index">Sedang diisi</small>
                </span>
              </button>
            </li>
          </ol>
        </aside>

        <div class="case-flow-main">
          <form
            v-if="step < steps.length"
            class="form-card case-form-card case-step-card"
            @submit.prevent="next"
          >
            <div class="case-step-mobile-count">
              TAHAP {{ step + 1 }} DARI {{ steps.length }}
            </div>
            <h1>{{ currentStep.title }}</h1>
            <p class="case-step-description">{{ currentStep.description }}</p>

            <div v-if="step === 0" class="form-grid case-data-grid">
              <label class="field-label span-two">
                Nama lengkap <span class="case-field-note">Sesuai KTP</span>
                <input
                  v-model.trim="form.applicant_detail.full_name"
                  class="input-control"
                  autocomplete="name"
                  required
                />
              </label>
              <label class="field-label span-two">
                Nomor Induk Kependudukan (NIK)
                <span class="case-field-note">16 digit</span>
                <input
                  v-model="form.applicant_detail.nik"
                  class="input-control"
                  inputmode="numeric"
                  maxlength="16"
                  autocomplete="off"
                  placeholder="Masukkan 16 digit NIK"
                  required
                />
              </label>
              <label class="field-label">
                Tanggal lahir
                <input
                  v-model="form.applicant_detail.birth_date"
                  class="input-control"
                  type="date"
                  required
                />
              </label>
              <label class="field-label span-two">
                Alamat domisili sesuai KTP
                <textarea
                  v-model.trim="form.applicant_detail.address"
                  class="input-control textarea"
                  autocomplete="street-address"
                  required
                />
              </label>
            </div>

            <div v-else-if="step === 1" class="form-grid case-data-grid">
              <label class="field-label span-two">
                Nama lengkap pasangan
                <span class="case-field-note">Sesuai KTP, jika diketahui</span>
                <input
                  v-model.trim="form.spouse_detail.full_name"
                  class="input-control"
                  required
                />
              </label>
              <label class="field-label span-two">
                Nomor Induk Kependudukan (NIK)
                <span class="optional">Opsional</span>
                <input
                  v-model="form.spouse_detail.nik"
                  class="input-control"
                  inputmode="numeric"
                  maxlength="16"
                  placeholder="Masukkan NIK jika diketahui"
                />
              </label>
              <label class="field-label">
                Nomor telepon <span class="optional">Opsional</span>
                <input
                  v-model="form.spouse_detail.phone"
                  class="input-control"
                  inputmode="tel"
                />
              </label>
              <label class="field-label">
                Pekerjaan <span class="optional">Opsional</span>
                <input
                  v-model="form.spouse_detail.occupation"
                  class="input-control"
                />
              </label>
              <label class="field-label span-two">
                Alamat domisili terakhir
                <textarea
                  v-model="form.spouse_detail.address"
                  class="input-control textarea"
                />
              </label>
            </div>

            <div v-else-if="step === 2" class="form-grid case-data-grid">
              <label class="field-label">
                Tanggal menikah
                <input
                  v-model="form.marriage_detail.marriage_date"
                  class="input-control"
                  type="date"
                  required
                />
              </label>
              <label class="field-label">
                Nomor Buku Nikah / Akta
                <input
                  v-model="form.marriage_detail.marriage_certificate_number"
                  class="input-control"
                />
              </label>
              <label class="field-label span-two">
                Tempat menikah (KUA / Catatan Sipil)
                <input
                  v-model="form.marriage_detail.kua_or_civil_registry"
                  class="input-control"
                />
              </label>
              <label class="field-label span-two">
                Alamat terakhir tinggal bersama
                <textarea
                  v-model="form.marriage_detail.last_address"
                  class="input-control textarea"
                />
              </label>
              <label class="check-field span-two case-children-toggle">
                <input
                  v-model="form.marriage_detail.has_children"
                  type="checkbox"
                />
                <span>
                  <b>Punya anak</b>
                  <small>Apakah dari pernikahan ini ada anak kandung?</small>
                </span>
              </label>
              <label
                v-if="form.marriage_detail.has_children"
                class="field-label"
              >
                Jumlah anak
                <input
                  v-model.number="form.marriage_detail.children_count"
                  class="input-control"
                  type="number"
                  min="1"
                />
              </label>
            </div>

            <div v-else-if="step === 3" class="case-reason-content">
              <label class="field-label">
                Pilih alasan utama
                <select
                  v-model="form.reason_category"
                  class="input-control"
                  required
                >
                  <option value="">Pilih alasan pengajuan</option>
                  <option
                    v-for="reason in reasons"
                    :key="reason"
                    :value="reason"
                  >
                    {{ reason }}
                  </option>
                </select>
              </label>
              <p class="case-helper-text">
                Pilih alasan yang sesuai dengan keadaanmu. Jika ragu, kamu dapat
                meminta penjelasan lebih lanjut kepada petugas pengadilan.
              </p>
              <label class="field-label">
                Ceritakan singkat <span class="optional">Opsional</span>
                <textarea
                  v-model="form.reason_detail"
                  class="input-control textarea case-reason-textarea"
                  maxlength="500"
                  placeholder="Tuliskan informasi yang ingin disampaikan kepada pengadilan."
                />
              </label>
              <div class="case-character-count">
                {{ form.reason_detail.length }} / 500 karakter
              </div>
              <div class="case-inline-note">
                Informasi ini masih bisa diperiksa kembali sebelum dikirim.
              </div>
            </div>

            <div v-else-if="step === 4" class="case-document-list">
              <label
                v-for="document in documents"
                :key="document.type"
                class="case-document-item"
              >
                <span class="case-document-copy">
                  <b>{{ document.title }}</b>
                  <small>
                    {{ selectedFiles[document.type]?.name || document.note }}
                  </small>
                </span>
                <span class="case-document-action">
                  {{ selectedFiles[document.type] ? "Ganti berkas" : "Pilih berkas" }}
                  <input
                    type="file"
                    accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                    @change="chooseFile(document.type, $event)"
                  />
                </span>
              </label>
              <p class="case-upload-summary">
                {{ filesReady }} dari {{ documents.length }} berkas dipilih.
                Kamu bisa mengganti berkas sebelum mengirim pengajuan.
              </p>
            </div>

            <div v-else class="case-court-content">
              <label class="field-label">
                Pengadilan tujuan
                <select
                  v-model="form.court_id"
                  class="input-control"
                  required
                >
                  <option value="">Pilih pengadilan sesuai domisili</option>
                  <option
                    v-for="court in courts"
                    :key="court.id"
                    :value="court.id"
                  >
                    {{ court.name }} · {{ court.city }}
                  </option>
                </select>
              </label>
              <label class="field-label">
                Radius panggilan
                <select v-model.number="form.radius" class="input-control">
                  <option
                    v-for="radius in [1, 2, 3, 4, 5]"
                    :key="radius"
                    :value="radius"
                  >
                    Radius {{ radius }}
                  </option>
                </select>
              </label>

              <section v-if="estimate" class="case-estimate-panel">
                <h2>Estimasi biaya panjar perkara</h2>
                <div class="case-estimate-row">
                  <span>Pendaftaran, proses, dan meterai</span>
                  <b>
                    {{
                      currency(
                        (estimate.breakdown?.biaya_pendaftaran_pnbp || 0) +
                          (estimate.breakdown?.biaya_proses_atk || 0) +
                          (estimate.breakdown?.biaya_redaksi || 0) +
                          (estimate.breakdown?.meterai || 0),
                      )
                    }}
                  </b>
                </div>
                <div class="case-estimate-row">
                  <span>Pemanggilan para pihak</span>
                  <b>
                    {{
                      currency(
                        (estimate.breakdown?.panggilan_pemohon?.subtotal || 0) +
                          (estimate.breakdown?.panggilan_termohon?.subtotal || 0),
                      )
                    }}
                  </b>
                </div>
                <div class="case-estimate-total">
                  <span>Total estimasi</span>
                  <strong>{{ currency(estimate.total_estimate) }}</strong>
                </div>
              </section>
              <p class="case-helper-text">
                Estimasi dapat berbeda sesuai radius tempat tinggal para pihak
                dan ketentuan pengadilan.
              </p>
            </div>

            <div v-if="error" class="inline-error" role="alert">
              {{ error }}
            </div>
            <div class="case-step-actions">
              <button
                type="button"
                class="btn btn-soft"
                @click="step === 0 ? (step = -1) : step--"
              >
                Kembali
              </button>
              <button class="btn btn-primary" type="submit">
                {{ step === steps.length - 1 ? "Tinjau pengajuan" : "Lanjut" }}
              </button>
            </div>
          </form>

          <form
            v-else
            class="form-card case-form-card case-review-card"
            @submit.prevent="submit"
          >
            <span class="eyebrow">TAHAP AKHIR</span>
            <h1>Tinjau pengajuan</h1>
            <p class="case-step-description">
              Pastikan seluruh informasi sesuai sebelum dikirim untuk diproses.
            </p>

            <div class="case-review-list">
              <section>
                <div>
                  <small>Data kamu</small>
                  <b>{{ form.applicant_detail.full_name }} · {{ form.applicant_detail.nik }}</b>
                </div>
                <button type="button" @click="editStep(0)">Ubah</button>
              </section>
              <section>
                <div>
                  <small>Data pasangan</small>
                  <b>{{ form.spouse_detail.full_name }}</b>
                  <span v-if="form.spouse_detail.address">
                    {{ form.spouse_detail.address }}
                  </span>
                </div>
                <button type="button" @click="editStep(1)">Ubah</button>
              </section>
              <section>
                <div>
                  <small>Pernikahan</small>
                  <b>
                    {{ form.marriage_detail.marriage_date }}
                    <template v-if="form.marriage_detail.has_children">
                      · {{ form.marriage_detail.children_count }} anak
                    </template>
                  </b>
                </div>
                <button type="button" @click="editStep(2)">Ubah</button>
              </section>
              <section>
                <div>
                  <small>Alasan perceraian</small>
                  <b>{{ form.reason_category }}</b>
                  <span v-if="form.reason_detail">{{ form.reason_detail }}</span>
                </div>
                <button type="button" @click="editStep(3)">Ubah</button>
              </section>
              <section>
                <div>
                  <small>Berkas</small>
                  <b>{{ filesReady }} berkas dipilih</b>
                </div>
                <button type="button" @click="editStep(4)">Ubah</button>
              </section>
              <section>
                <div>
                  <small>Pengadilan dan biaya</small>
                  <b>
                    {{ selectedCourt?.name || "Pengadilan" }} ·
                    {{ currency(estimate?.total_estimate) }}
                  </b>
                </div>
                <button type="button" @click="editStep(5)">Ubah</button>
              </section>
            </div>

            <div class="case-confirmation">
              <b>Data sudah benar</b>
              <p>
                Dengan mengirim pengajuan, kamu menyatakan informasi telah
                diperiksa dan sesuai dokumen yang dimiliki.
              </p>
            </div>
            <div v-if="error" class="inline-error" role="alert">
              {{ error }}
            </div>
            <div class="case-step-actions">
              <button type="button" class="btn btn-soft" @click="step = 5">
                Kembali
              </button>
              <button class="btn btn-primary" :disabled="busy">
                {{ busy ? "Mengirim pengajuan..." : "Kirim pengajuan" }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <p class="disclaimer">
        Bukan nasihat hukum resmi pengadilan. Layanan panduan administratif
        mandiri.
      </p>
    </div>
  </section>
</template>
