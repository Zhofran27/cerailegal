<script setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { api } from "../api";
import { apiError } from "../api/http";
const router = useRouter();
const step = ref(0);
const courts = ref([]);
const busy = ref(false);
const error = ref("");
const form = ref({
  court_id: "",
  reason: "",
  spouse_detail: { full_name: "", nik: "", phone: "", address: "" },
  marriage_detail: {
    marriage_date: "",
    marriage_certificate_number: "",
    kua_or_civil_registry: "",
    has_children: false,
    children_count: 0,
    last_address: "",
  },
});
const steps = ["Perkara", "Pasangan", "Perkawinan"];
const current = computed(() => steps[step.value]);
onMounted(async () => {
  try {
    const data = await api.courts();
    courts.value = Array.isArray(data) ? data : data?.data || [];
  } catch (e) {
    error.value = apiError(e);
  }
});
function next() {
  error.value = "";
  if (
    step.value === 0 &&
    (!form.value.court_id || form.value.reason.trim().length < 20)
  ) {
    error.value = !form.value.court_id
      ? "Pilih pengadilan terlebih dahulu."
      : "Ceritakan alasan pengajuan dengan minimal 20 karakter.";
    return;
  }
  if (step.value === 1 && !form.value.spouse_detail.full_name.trim()) {
    error.value = "Nama pasangan wajib diisi.";
    return;
  }
  if (step.value < 2) step.value++;
}
async function submit() {
  error.value = "";
  busy.value = true;
  try {
    const item = await api.createCase({
      ...form.value,
      status: "submitted",
      court_id: Number(form.value.court_id),
    });
    router.push(`/cases/${item.id}`);
    window.dispatchEvent(
      new CustomEvent("cerailegal:toast", {
        detail: "Pengajuan berhasil dikirim.",
      }),
    );
  } catch (e) {
    error.value = apiError(e);
  } finally {
    busy.value = false;
  }
}
</script>
<template>
  <section class="page-shell">
    <div class="container narrow-container">
      <RouterLink to="/dashboard" class="back-link">
        Kembali ke dashboard</RouterLink
      >
      <div class="form-card case-form-card">
        <div class="eyebrow">PENGAJUAN PERKARA</div>
        <h1>Lengkapi informasi dasar.</h1>
        <p class="page-intro">
          Kamu bisa menyiapkan dokumen setelah pengajuan dibuat.
        </p>
        <div class="wizard-steps">
          <div
            v-for="(name, i) in steps"
            :key="name"
            :class="['wizard-step', { active: step === i, complete: step > i }]"
            @click="step > i && (step = i)"
          >
            <span>{{ step > i ? "✓" : `0${i + 1}` }}</span
            >{{ name }}
          </div>
        </div>
        <form @submit.prevent="step < 2 ? next() : submit()">
          <template v-if="current === 'Perkara'"
            ><label class="field-label"
              >Pengadilan tujuan<select
                v-model="form.court_id"
                class="input-control"
                required
              >
                <option value="">Pilih pengadilan</option>
                <option
                  v-for="court in courts"
                  :key="court.id"
                  :value="court.id"
                >
                  {{ court.name }} — {{ court.city }}
                </option>
              </select></label
            ><label class="field-label"
              >Ceritakan alasan pengajuan<textarea
                v-model="form.reason"
                class="input-control textarea"
                minlength="20"
                required
                placeholder="Ceritakan secara singkat keadaan yang melatarbelakangi pengajuan…"
              ></textarea
              ><small
                >Informasi ini membantu pengadilan memahami pokok
                perkara.</small
              ></label
            ></template
          ><template v-else-if="current === 'Pasangan'"
            ><div class="form-grid">
              <label class="field-label span-two"
                >Nama lengkap pasangan<input
                  v-model.trim="form.spouse_detail.full_name"
                  class="input-control"
                  required /></label
              ><label class="field-label"
                >NIK <span class="optional">(opsional)</span
                ><input
                  v-model="form.spouse_detail.nik"
                  class="input-control"
                  maxlength="16"
                  inputmode="numeric" /></label
              ><label class="field-label"
                >Nomor telepon <span class="optional">(opsional)</span
                ><input
                  v-model="form.spouse_detail.phone"
                  class="input-control"
                  inputmode="tel" /></label
              ><label class="field-label span-two"
                >Alamat terakhir pasangan<textarea
                  v-model="form.spouse_detail.address"
                  class="input-control textarea"
                  placeholder="Alamat sesuai informasi yang kamu miliki"
                ></textarea>
              </label></div></template
          ><template v-else
            ><div class="form-grid">
              <label class="field-label"
                >Tanggal perkawinan<input
                  v-model="form.marriage_detail.marriage_date"
                  class="input-control"
                  type="date" /></label
              ><label class="field-label"
                >Nomor Buku Nikah / Akta<input
                  v-model="form.marriage_detail.marriage_certificate_number"
                  class="input-control" /></label
              ><label class="field-label span-two"
                >KUA / Catatan Sipil<input
                  v-model="form.marriage_detail.kua_or_civil_registry"
                  class="input-control" /></label
              ><label class="field-label span-two"
                >Alamat terakhir bersama<input
                  v-model="form.marriage_detail.last_address"
                  class="input-control" /></label
              ><label class="check-field span-two"
                ><input
                  v-model="form.marriage_detail.has_children"
                  type="checkbox"
                />
                Kami memiliki anak</label
              ><label
                v-if="form.marriage_detail.has_children"
                class="field-label"
                >Jumlah anak<input
                  v-model.number="form.marriage_detail.children_count"
                  class="input-control"
                  type="number"
                  min="1"
              /></label>
            </div>
            <p class="privacy-hint">
              Data tersimpan privat dalam akunmu. Pastikan informasi yang
              dimasukkan sudah benar.
            </p></template
          >
          <div v-if="error" class="inline-error">{{ error }}</div>
          <div class="form-actions">
            <button
              v-if="step > 0"
              type="button"
              class="btn btn-outline"
              @click="step--"
            >
              Kembali</button
            ><span v-else></span
            ><button class="btn btn-primary" :disabled="busy">
              {{
                busy
                  ? "Mengirim…"
                  : step === 2
                    ? "Kirim pengajuan"
                    : "Lanjutkan"
              }}
              <span></span>
            </button>
          </div>
        </form>
      </div>
      <p class="disclaimer">
        Pengajuan akan ditinjau sesuai prosedur pengadilan yang berwenang.
      </p>
    </div>
  </section>
</template>
