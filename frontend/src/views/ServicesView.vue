<script setup>
const services = [
  {
    title: "Cek Persiapan",
    text: "Membantu mengetahui kelengkapan dokumen dan informasi yang perlu dipersiapkan sebelum mengajukan perceraian.",
    icon: "check",
  },
  {
    title: "Informasi Persyaratan",
    text: "Menyediakan informasi mengenai dokumen dan persyaratan administrasi untuk pengajuan perceraian.",
    icon: "document",
  },
  {
    title: "Panduan Pengajuan",
    text: "Memberikan penjelasan mengenai tahapan pengajuan perceraian dari awal hingga proses selesai.",
    icon: "path",
  },
  {
    title: "Informasi Biaya",
    text: "Memberikan gambaran mengenai rincian biaya yang mungkin diperlukan selama proses perceraian.",
    icon: "wallet",
  },
  {
    title: "Pelacakan Pengajuan",
    text: "Membantu pengguna mengetahui perkembangan dan status proses pengajuan perceraian.",
    icon: "search",
  },
];
</script>

<template>
  <section class="page-shell services-page">
    <div class="container services-container">
      <header class="services-heading">
        <span class="services-eyebrow">LAYANAN PUBLIK</span>
        <h1>Layanan CeraiLegal</h1>
        <p class="services-intro">
          Kami menyediakan layanan untuk membantu kamu memahami dan
          mempersiapkan proses pengajuan perceraian dengan lebih mudah.
        </p>
      </header>

      <div class="services-list-heading">
        <span>Layanan yang tersedia</span>
      </div>

      <div class="services-list">
        <article
          v-for="(service, index) in services"
          :key="service.title"
          class="service-row"
        >
          <span class="service-row-icon" aria-hidden="true">
            <svg v-if="service.icon === 'check'" viewBox="0 0 24 24">
              <path
                d="M9 4.5h6a2 2 0 0 1 2 2V8h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1h2V6.5a2 2 0 0 1 2-2Z"
              />
              <path d="M9 4.5a3 3 0 0 1 6 0M8.5 14l2.2 2.2 4.8-5" />
            </svg>
            <svg v-else-if="service.icon === 'document'" viewBox="0 0 24 24">
              <rect x="4" y="3" width="16" height="19" rx="3" />
              <path d="M9 9h6M9 13h6" />
            </svg>
            <svg v-else-if="service.icon === 'path'" viewBox="0 0 24 24">
              <circle cx="6" cy="18" r="3" />
              <circle cx="18" cy="6" r="3" />
              <path d="M9 18h2a4 4 0 0 0 4-4v-1a4 4 0 0 1 4-4M6 15V7" />
            </svg>
            <svg v-else-if="service.icon === 'wallet'" viewBox="0 0 24 24">
              <rect x="3" y="6" width="18" height="15" rx="2" />
              <path d="M3 9h18M7 6V4h12M15 14h6" />
              <circle cx="16" cy="14" r=".7" />
            </svg>
            <svg v-else viewBox="0 0 24 24">
              <circle cx="10.5" cy="10.5" r="7.5" />
              <path d="m16 16 5 5M8 10.5l1.7 1.7 3.6-3.8" />
            </svg>
          </span>
          <span class="service-row-copy">
            <span class="service-index">LAYANAN 0{{ index + 1 }}</span>
            <b>{{ service.title }}</b>
            <span>{{ service.text }}</span>
          </span>
        </article>
      </div>
    </div>
  </section>
</template>
