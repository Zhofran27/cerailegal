<script setup>
const services = [
  {
    title: "Cek Persiapan",
    text: "Membantu mengetahui kelengkapan dokumen dan informasi yang perlu dipersiapkan sebelum mengajukan perceraian.",
  },
  {
    title: "Informasi Persyaratan",
    text: "Menyediakan informasi mengenai dokumen dan persyaratan administrasi untuk pengajuan perceraian.",
  },
  {
    title: "Panduan Pengajuan",
    text: "Memberikan penjelasan mengenai tahapan pengajuan perceraian dari awal hingga proses selesai.",
  },
  {
    title: "Informasi Biaya",
    text: "Memberikan gambaran mengenai rincian biaya yang mungkin diperlukan selama proses perceraian.",
  },
  {
    title: "Pelacakan Pengajuan",
    text: "Membantu pengguna mengetahui perkembangan dan status proses pengajuan perceraian.",
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
