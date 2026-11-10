<script setup>
import { computed, ref } from "vue";
import { RouterLink } from "vue-router";
import { api } from "../api";
import { apiError } from "../api/http";

const step = ref(0);
const loading = ref(false);
const result = ref(null);
const error = ref("");
const answers = ref({
  has_marriage_certificate: null,
  has_legal_reason: null,
  is_separated: null,
  spouse_address_known: null,
});
const questions = [
  {
    key: "has_marriage_certificate",
    title: "Apakah kamu memiliki Buku Nikah atau Akta Perkawinan?",
    hint: "Dokumen ini menjadi bukti resmi perkawinan.",
  },
  {
    key: "has_legal_reason",
    title: "Apakah ada alasan yang menjadi dasar pengajuan perceraian?",
    hint: "Contohnya perselisihan berkepanjangan, penelantaran, atau kekerasan.",
  },
  {
    key: "is_separated",
    title: "Apakah kamu dan pasangan sudah tinggal terpisah?",
    hint: "Jawaban ini membantu memberi konteks, bukan penentu kelayakan.",
  },
  {
    key: "spouse_address_known",
    title: "Apakah alamat pasangan saat ini diketahui?",
    hint: "Jika tidak diketahui, ada prosedur khusus yang bisa ditanyakan.",
  },
];
const current = computed(() => questions[step.value]);
async function next() {
  error.value = "";
  if (answers.value[current.value.key] === null) {
    error.value = "Pilih salah satu jawaban untuk melanjutkan.";
    return;
  }
  if (step.value < questions.length - 1) {
    step.value++;
    return;
  }
  loading.value = true;
  try {
    result.value = await api.eligibility(answers.value);
  } catch (e) {
    error.value = apiError(e);
  } finally {
    loading.value = false;
  }
}
function back() {
  if (result.value) result.value = null;
  else if (step.value > 0) step.value--;
}
function reset() {
  step.value = 0;
  result.value = null;
  error.value = "";
  answers.value = {
    has_marriage_certificate: null,
    has_legal_reason: null,
    is_separated: null,
    spouse_address_known: null,
  };
}
</script>

<template>
  <section class="page-shell">
    <div class="container narrow-container">
      <RouterLink to="/" class="back-link"> Kembali ke beranda</RouterLink>
      <div class="form-card">
        <div class="form-kicker">PANDUAN AWAL <span>•</span> 2 MENIT</div>
        <template v-if="!result"
          ><div class="progress-row">
            <span>PERTANYAAN {{ step + 1 }} DARI {{ questions.length }}</span
            ><span>{{ Math.round((step / questions.length) * 100) }}%</span>
          </div>
          <div class="progress-track">
            <span
              :style="{ width: `${((step + 1) / questions.length) * 100}%` }"
            ></span>
          </div>
          <div class="question-content">
            <h1>{{ current.title }}</h1>
            <p>{{ current.hint }}</p>
            <div class="answer-list">
              <button
                v-for="option in [
                  { label: 'Ya', value: true },
                  { label: 'Tidak', value: false },
                ]"
                :key="String(option.value)"
                :class="[
                  'answer-option',
                  { selected: answers[current.key] === option.value },
                ]"
                @click="answers[current.key] = option.value"
              >
                <span class="radio-dot"></span>{{ option.label
                }}<span class="answer-arrow"></span>
              </button>
            </div>
            <div v-if="error" class="inline-error" role="alert">
              {{ error }}
            </div>
            <div class="form-actions">
              <button v-if="step > 0" class="btn btn-outline" @click="back">
                Kembali</button
              ><span v-else></span
              ><button
                class="btn btn-primary"
                :disabled="loading"
                @click="next"
              >
                {{
                  loading
                    ? "Memeriksa…"
                    : step === questions.length - 1
                      ? "Lihat hasil"
                      : "Lanjutkan"
                }}
                <span></span>
              </button>
            </div>
          </div>
          <p class="privacy-hint">
            Jawabanmu hanya digunakan untuk memberi panduan awal.
          </p>
        </template>
        <template v-else
          ><div class="eyebrow">HASIL PANDUAN AWAL</div>
          <h1>
            {{
              result.is_eligible
                ? "Kamu dapat melanjutkan persiapan."
                : "Ada beberapa hal untuk dipersiapkan."
            }}
          </h1>
          <p class="result-lead">{{ result.message }}</p>
          <div v-if="result.unmet_criteria?.length" class="result-list">
            <h3>Yang perlu diperhatikan</h3>
            <p v-for="item in result.unmet_criteria" :key="item">{{ item }}</p>
          </div>
          <div v-if="result.recommendations?.length" class="result-list">
            <h3>Rekomendasi</h3>
            <p v-for="item in result.recommendations" :key="item">{{ item }}</p>
          </div>
          <div v-if="result.next_steps?.length" class="result-list">
            <h3>Langkah berikutnya</h3>
            <p v-for="item in result.next_steps" :key="item">{{ item }}</p>
          </div>
          <div class="form-actions result-actions">
            <button class="btn btn-outline" @click="reset">Ulangi cek</button
            ><RouterLink
              v-if="result.is_eligible"
              to="/dashboard"
              class="btn btn-primary"
              >Lanjutkan pengajuan <span></span></RouterLink
            ><RouterLink v-else to="/faqs" class="btn btn-primary"
              >Baca panduan <span></span
            ></RouterLink>
          </div>
        </template>
      </div>
      <p class="disclaimer">
        Hasil ini merupakan informasi awal dan bukan keputusan pengadilan atau
        nasihat hukum.
      </p>
    </div>
  </section>
</template>
