<script setup>
import { computed } from "vue";
import { RouterLink } from "vue-router";
const props = defineProps({ page: { type: String, required: true } });
const pages = {
  workflow: {
    eyebrow: "ALUR TAHAPAN",
    title: "Satu langkah dalam satu waktu.",
    intro:
      "Kenali perjalanan perkara sejak persiapan sampai putusan. Waktu dan tahapan dapat berbeda sesuai kondisi masing-masing perkara.",
    steps: [
      [
        "01",
        "Kenali kebutuhanmu",
        "Pelajari informasi dasar dan cek kelayakan awal.",
      ],
      [
        "02",
        "Siapkan dokumen",
        "Kumpulkan identitas, bukti perkawinan, dan dokumen pendukung.",
      ],
      [
        "03",
        "Ajukan ke pengadilan",
        "Daftarkan gugatan atau permohonan pada pengadilan yang berwenang.",
      ],
      [
        "04",
        "Ikuti proses persidangan",
        "Pengadilan memanggil para pihak dan mengupayakan mediasi.",
      ],
      [
        "05",
        "Putusan dan tindak lanjut",
        "Ikuti pemberitahuan putusan serta proses administrasi setelahnya.",
      ],
    ],
  },
  mediation: {
    eyebrow: "DIALOG MEDIASI",
    title: "Ruang untuk mencari jalan bersama.",
    intro:
      "Mediasi adalah proses perundingan yang dibantu mediator netral. Dalam perkara perdata, mediasi umumnya menjadi bagian dari proses di pengadilan.",
    steps: [
      [
        "01",
        "Kehadiran para pihak",
        "Hakim mediator menjelaskan proses dan memberi ruang bagi setiap pihak.",
      ],
      [
        "02",
        "Sampaikan kepentingan",
        "Kedua pihak dapat menyampaikan hal yang penting bagi mereka dengan tertib.",
      ],
      [
        "03",
        "Cari kemungkinan solusi",
        "Mediator membantu para pihak mengeksplorasi pilihan secara sukarela.",
      ],
      [
        "04",
        "Kesepakatan atau lanjut sidang",
        "Jika sepakat, hasil dicatat. Jika belum, perkara berlanjut ke pemeriksaan.",
      ],
    ],
  },
  privacy: {
    eyebrow: "PRIVASI & KEAMANAN",
    title: "Informasi pribadimu layak dilindungi.",
    intro:
      "CeraiLegal meminta data hanya untuk membantu proses layanan. Dokumen perkara disimpan sebagai dokumen privat dan hanya dapat diakses melalui akun yang berwenang.",
    steps: [
      [
        "Data akun",
        "Informasi yang kamu berikan saat mendaftar digunakan untuk menyediakan layanan.",
      ],
      [
        "Dokumen perkara",
        "Berkas yang diunggah tidak ditampilkan sebagai file publik.",
      ],
      [
        "Akses dan keamanan",
        "Jaga kerahasiaan kata sandi dan gunakan perangkat pribadi saat mengakses akun.",
      ],
      [
        "Pertanyaan",
        "Hubungi pengelola layanan jika kamu memiliki pertanyaan terkait data.",
      ],
    ],
  },
};
const content = computed(() => pages[props.page]);
</script>
<template>
  <section class="page-shell">
    <div class="container content-container">
      <header class="services-heading info-heading-panel">
        <span class="services-eyebrow">{{ content.eyebrow }}</span>
        <h1>{{ content.title }}</h1>
        <p class="services-intro">{{ content.intro }}</p>
      </header>
      <div class="timeline">
        <article
          v-for="(step, i) in content.steps"
          :key="i"
          class="timeline-item"
        >
          <div class="timeline-index">
            {{
              typeof step[0] === "string" && step[0].length === 2
                ? step[0]
                : String(i + 1).padStart(2, "0")
            }}
          </div>
          <div>
            <h2>{{ step[1] || step[0] }}</h2>
            <p>{{ step[2] || step[1] }}</p>
          </div>
        </article>
      </div>
      <div class="info-callout">
        <span>✳</span>
        <p>
          Informasi ini bersifat umum. Detail proses dapat berbeda sesuai
          pengadilan dan kondisi perkara.
        </p>
      </div>
      <RouterLink to="/eligibility" class="btn btn-primary"
        >Cek Persiapan</RouterLink
      >
    </div>
  </section>
</template>
