# CeraiLegal ⚖️

> **Platform Pendamping & Pengajuan Perkara Perceraian yang Transparan, Humanis, dan Berorientasi pada Warga.**

---

## 📌 Deskripsi Singkat

**CeraiLegal** adalah aplikasi layanan hukum publik digital yang dirancang untuk menjembatani masyarakat pencari keadilan dengan proses peradilan perkara perceraian (Pengadilan Agama / Pengadilan Negeri). Mengusung pendekatan bahasa sehari-hari yang mudah dipahami, CeraiLegal mengedepankan transparansi proses, keterbukaan estimasi biaya panjar, serta keamanan data privat pemohon.

Platform ini menyediakan bantuan komprehensif mulai dari pengecekan mandiri kelayakan perkara (*eligibility check*), panduan alur dan hak hukum, kalkulator perkiraan biaya, formulir pengajuan perkara bertahap (*multi-step wizard*), unggah dokumen pembuktian, pelacakan status penanganan perkara (*case tracking*), hingga panel verifikasi dan peninjauan dokumen perkara untuk pihak pengadilan/administrator.

---

## 🛠️ Tech Stack yang Digunakan

### **Backend**
- **Framework**: [Laravel 12](https://laravel.com/) (PHP 8.2+)
- **Authentication**: Laravel Sanctum (Token-based API Authentication)
- **Role & Permission**: `spatie/laravel-permission` (Role: `admin` & `warga`)
- **Activity & Audit Logging**: `spatie/laravel-activitylog`
- **Database ORM**: Eloquent ORM dengan transaksi database atomik
- **Database Engine**: MySQL / MariaDB (kompatibel juga dengan SQLite untuk testing/development)

### **Frontend**
- **Framework**: [Vue 3](https://vuejs.org/) (Composition API, `<script setup>`)
- **Build Tool**: [Vite](https://vitejs.dev/)
- **Routing**: [Vue Router 4](https://router.vuejs.org/) (dilengkapi *navigation guards* berbasis otentikasi)
- **State Management**: [Pinia](https://pinia.vuejs.org/)
- **Styling**: [Tailwind CSS](https://tailwindcss.com/) & Custom CSS Design System
- **HTTP Client**: [Axios](https://axios-http.com/) dengan response/request interceptors

---

## 📁 Struktur Monorepo

```text
cerailegal/
├── backend/                  # Laravel 12 API Server
│   ├── app/
│   │   ├── Http/Controllers/ # Auth, Case, Document, Public, Admin Controllers
│   │   ├── Http/Requests/    # Form Request Validation
│   │   ├── Http/Resources/   # JSON API Resources Transformer
│   │   └── Models/           # LegalCase, Court, Document, SpouseDetail, dsb.
│   ├── database/
│   │   ├── migrations/       # Skema database relasional perkara & pengguna
│   │   └── seeders/          # Seeder Role & Akun Administrator
│   └── routes/api.php        # Definisi rute RESTful API (Public, Warga, Admin)
├── frontend/                 # Vue 3 + Vite SPA Client
│   ├── src/
│   │   ├── api/              # Klien API terpusat & error handling
│   │   ├── router/           # Konfigurasi routing & auth protection
│   │   ├── stores/           # Pinia store (manajemen sesi & auth pengguna)
│   │   └── views/            # Komponen halaman (Home, Eligibility, Dashboard, Kasus, dll.)
└── README.md                 # Dokumentasi proyek
```

---

## 🚀 Cara Menjalankan Project

### 1. Prasyarat Sistem
Pastikan perangkat Anda telah terpasang:
- **PHP** >= 8.2
- **Composer** (PHP Package Manager)
- **Node.js** >= 18.x & **npm**
- **MySQL / MariaDB** (atau SQLite)

---

### 2. Menjalankan Backend (Laravel API)

1. **Buka terminal dan masuk ke folder `backend`:**
   ```bash
   cd backend
   ```

2. **Instal dependensi PHP:**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment:**
   Duplikat file `.env.example` menjadi `.env` (jika belum ada):
   ```bash
   cp .env.example .env
   ```
   Pastikan pengaturan koneksi database di `.env` sudah sesuai:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cerailegal
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan Migrasi Database & Seeder:**
   ```bash
   php artisan migrate --seed
   ```
   > Seeder akan otomatis membuat role `admin` dan `warga`, serta akun super admin default:
   > - **Email**: `admin@cerailegal.com`
   > - **Password**: `password123`

6. **Jalankan Server Backend:**
   ```bash
   php artisan serve
   ```
   Backend akan berjalan di: `http://127.0.0.1:8000`

---

### 3. Menjalankan Frontend (Vue 3 + Vite)

1. **Buka terminal baru dan masuk ke folder `frontend`:**
   ```bash
   cd frontend
   ```

2. **Instal paket dependensi Node:**
   ```bash
   npm install
   ```

3. **Periksa Konfigurasi Environment:**
   Pastikan file `.env` di folder `frontend` mengarah ke backend API:
   ```env
   VITE_API_BASE_URL=http://127.0.0.1:8000/api
   ```

4. **Jalankan Development Server:**
   ```bash
   npm run dev
   ```
   Frontend akan berjalan di: `http://localhost:5173`

---

## 📊 Progress Saat Ini

| Modul / Fitur | Status | Deskripsi |
| :--- | :---: | :--- |
| **Landing Page & Informasi Publik** | ✅ Selesai | Halaman beranda humanis, transparansi data statistik perkara, panduan mediasi, daftar pengadilan, dan FAQ interaktif. |
| **Pengecekan Kelayakan (*Eligibility Checker*)** | ✅ Selesai | Evaluasi otomatis kesiapan syarat hukum perkara warga (akta/buku nikah, alasan hukum yang sah, status keberadaan pasangan). |
| **Autentikasi & Autorisasi (Sanctum + RBAC)** | ✅ Selesai | Registrasi & login token-based, pembagian akses terpisah untuk role `warga` dan `admin`. |
| **Pengajuan Perkara Warga (*Wizard Form*)** | 🔄 Terintegrasi / On-Progress | Formulir pengajuan terstruktur mencakup data identitas pemohon, data pihak pasangan, riwayat pernikahan, serta alasan hukum. |
| **Manajemen & Unggah Dokumen Berkas** | 🔄 Terintegrasi / On-Progress | Unggah dokumen bukti (KTP, Buku Nikah, KK, Bukti Persidangan) dan pengunduhan berkas secara aman (*authorized download*). |
| **Pelacakan Status Perkara (*Case Tracking*)** | 🔄 Terintegrasi / On-Progress | Riwayat pembaruan status perkara transparan yang dicatat berkala (*case status logs*). |
| **Dashboard Warga** | 🔄 Terintegrasi / On-Progress | Antarmuka personal warga untuk memantau ringkasan seluruh perkara yang pernah diajukan. |
| **Admin Case Management (Backend API)** | 🔄 Terintegrasi / On-Progress | Endpoint pencarian pintar perkara, penyaringan status, peninjauan berkas warga, dan pembaruan status perkara oleh admin. |
| **Portal Khusus Admin UI** | 🔄 Terintegrasi / On-Progress | Penyempurnaan dashboard antarmuka khusus petugas pengadilan untuk review berkas terpusat secara visual. |
| **Notifikasi Otomatis (Email / WhatsApp Gateway)** | ⏳ Rencana Pengembangan | Pengiriman update otomatis saat status perkara berubah dari tahap pengajuan hingga putusan. |

---

## 🔒 Keamanan & Perlindungan Privasi
- Seluruh dokumen dan informasi perkara dilindungi oleh autentikasi berbasis kepemilikan data (*policy/authorization check*).
- Hanya pemohon yang sah atau administrator pengadilan yang berhak melihat dan mengunduh berkas perkara terkait.
- Menggunakan parameter validasi ketat (*Form Request Validation*) untuk menangkal input tidak valid atau berbahaya.

