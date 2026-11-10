<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Apa syarat utama mengajukan perceraian?',
                'answer' => 'Syarat utama meliputi identitas diri (KTP & KK), Buku Nikah atau Akta Perkawinan asli, serta adanya alasan hukum perceraian yang sah sesuai peraturan perundang-undangan (seperti perselisihan terus-menerus, penelantaran, atau KDRT).',
                'category' => 'Persyaratan',
                'order' => 1,
            ],
            [
                'question' => 'Apa perbedaan Pengadilan Agama dan Pengadilan Negeri?',
                'answer' => 'Pengadilan Agama berwenang memutus perkara perceraian bagi pasangan yang menikah secara Islam. Sedangkan Pengadilan Negeri berwenang menangani perceraian bagi pasangan yang menikah secara non-Islam (Kristen, Katolik, Hindu, Buddha, Konghucu).',
                'category' => 'Pengadilan',
                'order' => 2,
            ],
            [
                'question' => 'Berapa taksiran biaya panjar perkara perceraian?',
                'answer' => 'Biaya panjar perkara bervariasi bergantung pada radius jarak tempat tinggal pemohon dan termohon ke pengadilan. Estimasi panjar berkisar antara Rp 700.000 hingga Rp 1.500.000. Sisa panjar yang tidak terpakai setelah putusan akan dikembalikan kepada pemohon.',
                'category' => 'Biaya',
                'order' => 3,
            ],
            [
                'question' => 'Apakah proses mediasi di pengadilan itu wajib?',
                'answer' => 'Ya, sesuai PERMA No. 1 Tahun 2016, mediasi wajib ditempuh pada sidang pertama apabila kedua belah pihak (penggugat dan tergugat) hadir di persidangan. Mediator akan memfasilitasi upaya perdamaian.',
                'category' => 'Prosedur',
                'order' => 4,
            ],
            [
                'question' => 'Bagaimana jika pasangan tidak diketahui alamatnya (Ghaib)?',
                'answer' => 'Perkara dapat diajukan dengan mekanisme Perkara Ghaib. Pemohon wajib menyertakan Surat Keterangan Ghaib dari Kantor Kelurahan/Desa setempat. Pemanggilan akan dilakukan melalui media massa atau papan pengumuman resmi pengadilan.',
                'category' => 'Prosedur',
                'order' => 5,
            ],
            [
                'question' => 'Apakah saya bisa mengajukan perceraian tanpa biaya (Prodeo)?',
                'answer' => 'Bisa. Bagi warga yang kurang mampu secara ekonomi, Anda berhak mengajukan permohonan berperkara secara cuma-cuma (Prodeo) dengan melampirkan Surat Keterangan Tidak Mampu (SKTM) dari Kelurahan atau kartu jaminan sosial (KIP/KIS/PKH).',
                'category' => 'Biaya',
                'order' => 6,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::firstOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}

