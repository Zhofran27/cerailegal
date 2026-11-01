<script setup>
import { onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import { api } from "../api";

const stats = ref(null);
const loading = ref(true);
onMounted(async () => {
  try {
    stats.value = await api.statistics();
  } catch {
  } finally {
    loading.value = false;
  }
});
const fmt = (v) => new Intl.NumberFormat("id-ID").format(v || 0);
</script>

<template>
  <section class="hero">
    <div class="hero-inner">
      <div class="hero-copy">
        <div class="eyebrow">
          <span class="eyebrow-dot"></span> PENDAMPING PERKARA YANG MEMAHAMI
          ANDA
        </div>
        <h1>Pahami setiap tahap<br />perkaramu.</h1>
        <p class="hero-subtitle">
          Pendamping perceraian dalam bahasa sehari-hari.
        </p>
        <p class="hero-description">
          Temukan informasi yang kamu butuhkan, pahami pilihanmu, dan jalani
          setiap langkah dengan lebih tenang.
        </p>
        <div class="hero-actions">
          <RouterLink to="/cases/create" class="btn btn-primary btn-large"
            >Ajukan Perceraian</RouterLink
          >
        </div>
        <div class="trust-note">
          <span class="trust-icon">✓</span> Aman & privat <i></i> Gratis untuk
          memulai <i></i> Bahasa yang mudah dipahami
        </div>
      </div>
      <div class="hero-art" aria-hidden="true">
        <div class="art-halo"></div>
        <div class="art-sun"></div>
        <div class="art-building">
          <div class="building-roof"></div>
          <div class="building-top">
            <span></span><span></span><span></span>
          </div>
          <div class="building-columns"><b></b><b></b><b></b><b></b></div>
          <div class="building-steps"></div>
        </div>
        <div class="art-leaf leaf-one"></div>
        <div class="art-leaf leaf-two"></div>
        <div class="art-card">
          <span class="art-check">✓</span
          ><span
            ><b>Langkah demi langkah</b
            ><small>Informasi jelas, proses transparan</small></span
          >
        </div>
        <div class="art-caption">PENGADILAN AGAMA · INDONESIA</div>
      </div>
    </div>
    <div class="hero-bottom">
      <span>Informasi resmi, disampaikan dengan lebih manusiawi</span>
    </div>
  </section>

  <section class="section section-white" id="layanan">
    <div class="container">
      <div class="section-heading">
        <div>
          <span class="eyebrow">MULAI DARI YANG PALING PENTING</span>
          <h2>Temukan langkah yang tepat untukmu.</h2>
        </div>
        <p>
          Tak perlu memahami semuanya sekaligus. Mulai dari kebutuhanmu hari
          ini.
        </p>
      </div>
      <div class="service-grid">
        <RouterLink to="/eligibility" class="service-card featured"
          ><span class="service-number">01 / PERSIAPAN</span
          ><span class="service-icon">◎</span>
          <h3>Cek Persiapan</h3>
          <p>
            Kenali syarat dasar dan dokumen yang perlu disiapkan sebelum
            mengajukan perkara.
          </p>
          <span class="card-link">Mulai cek</span></RouterLink
        >
        <RouterLink to="/status" class="service-card"
          ><span class="service-number">02 / PELACAKAN</span
          ><span class="service-icon">⌕</span>
          <h3>Cek status perkara</h3>
          <p>Lihat perkembangan perkara dan riwayat pembaruan statusnya.</p>
          <span class="card-link">Lacak perkara <b></b></span
        ></RouterLink>
        <RouterLink to="/cost-estimate" class="service-card"
          ><span class="service-number">03 / PERENCANAAN</span
          ><span class="service-icon">Rp</span>
          <h3>Simulasi biaya</h3>
          <p>
            Dapatkan gambaran taksiran panjar berdasarkan pengadilan dan radius.
          </p>
          <span class="card-link">Hitung biaya <b></b></span
        ></RouterLink>
        <RouterLink to="/courts" class="service-card"
          ><span class="service-number">04 / DIREKTORI</span
          ><span class="service-icon">⌖</span>
          <h3>Cari pengadilan</h3>
          <p>Temukan pengadilan yang sesuai dengan domisili dan kebutuhanmu.</p>
          <span class="card-link">Lihat direktori <b></b></span
        ></RouterLink>
      </div>
    </div>
  </section>

  <section class="section stats-section" id="transparansi">
    <div class="container stats-layout">
      <div>
        <span class="eyebrow">KETERBUKAAN INFORMASI</span>
        <h2>Proses yang jelas<br />membangun rasa percaya.</h2>
        <p>
          Ringkasan data perkara dan layanan publik, diperbarui dari sistem.
        </p>
        <RouterLink to="/workflow" class="text-link"
          >Lihat Alur Tahapan <span></span
        ></RouterLink>
      </div>
      <div class="stat-grid">
        <div class="stat-card">
          <span class="stat-icon"></span
          ><strong>{{ loading ? "—" : fmt(stats?.total_cases) }}</strong
          ><span>Perkara tercatat</span><small>Agregat perkara platform</small>
        </div>
        <div class="stat-card">
          <span class="stat-icon">⌂</span
          ><strong>{{ loading ? "—" : fmt(stats?.total_courts) }}</strong
          ><span>Pengadilan terdaftar</span><small>Direktori layanan</small>
        </div>
        <div class="stat-card">
          <span class="stat-icon">✓</span
          ><strong>{{ loading ? "—" : fmt(stats?.summary?.selesai) }}</strong
          ><span>Perkara selesai</span><small>Termasuk putusan</small>
        </div>
        <div class="stat-card">
          <span class="stat-icon">?</span
          ><strong>{{ loading ? "—" : fmt(stats?.total_faqs) }}</strong
          ><span>Informasi tersedia</span><small>FAQ dan glosarium</small>
        </div>
      </div>
    </div>
  </section>
  <section class="closing-cta">
    <div class="container cta-inner">
      <div>
        <span class="eyebrow">LANGKAH PERTAMA, DENGAN TENANG</span>
        <h2>Setiap perjalanan dimulai dengan memahami pilihanmu.</h2>
      </div>
      <RouterLink to="/eligibility" class="btn btn-light btn-large"
        >Cek Persiapan</RouterLink
      >
    </div>
  </section>
</template>
