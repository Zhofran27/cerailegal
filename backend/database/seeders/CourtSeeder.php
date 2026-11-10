<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

class CourtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courts = [
            [
                'name' => 'Pengadilan Agama Jakarta Selatan',
                'code' => 'PA.JS',
                'address' => 'Jl. TB Simatupang No. 1, Pasar Minggu, Jakarta Selatan',
                'city' => 'Jakarta Selatan',
                'phone' => '(021) 78844055',
            ],
            [
                'name' => 'Pengadilan Agama Jakarta Timur',
                'code' => 'PA.JT',
                'address' => 'Jl. PKP No. 24, Kelapa Dua Wetan, Ciracas, Jakarta Timur',
                'city' => 'Jakarta Timur',
                'phone' => '(021) 8729177',
            ],
            [
                'name' => 'Pengadilan Agama Jakarta Barat',
                'code' => 'PA.JB',
                'address' => 'Jl. Puri Kencana Blok A No. 1, Kembangan, Jakarta Barat',
                'city' => 'Jakarta Barat',
                'phone' => '(021) 58356977',
            ],
            [
                'name' => 'Pengadilan Agama Jakarta Pusat',
                'code' => 'PA.JP',
                'address' => 'Jl. Rawasari Selatan No. 1, Cempaka Putih, Jakarta Pusat',
                'city' => 'Jakarta Pusat',
                'phone' => '(021) 4241777',
            ],
            [
                'name' => 'Pengadilan Agama Jakarta Utara',
                'code' => 'PA.JU',
                'address' => 'Jl. Pluit Raya No. 1, Pluit, Penjaringan, Jakarta Utara',
                'city' => 'Jakarta Utara',
                'phone' => '(021) 6625888',
            ],
            [
                'name' => 'Pengadilan Agama Depok',
                'code' => 'PA.DPK',
                'address' => 'Jl. Boulevard Sektor Anggrek, Grand Depok City, Depok',
                'city' => 'Depok',
                'phone' => '(021) 77823456',
            ],
            [
                'name' => 'Pengadilan Agama Kota Bekasi',
                'code' => 'PA.BKS',
                'address' => 'Jl. KH. Noer Ali No. 2, Kayuringin Jaya, Bekasi Selatan',
                'city' => 'Bekasi',
                'phone' => '(021) 88950123',
            ],
            [
                'name' => 'Pengadilan Agama Kota Bandung',
                'code' => 'PA.BDG',
                'address' => 'Jl. Terusan Terusan Jakarta No. 120, Antapani, Bandung',
                'city' => 'Bandung',
                'phone' => '(022) 7208888',
            ],
            [
                'name' => 'Pengadilan Agama Surabaya',
                'code' => 'PA.SBY',
                'address' => 'Jl. Ketintang Madya VI No. 3, Gayungan, Surabaya',
                'city' => 'Surabaya',
                'phone' => '(031) 8290123',
            ],
            [
                'name' => 'Pengadilan Negeri Jakarta Pusat',
                'code' => 'PN.JKT.PST',
                'address' => 'Jl. Bungur Besar Raya No. 24, Gunung Sahari, Jakarta Pusat',
                'city' => 'Jakarta Pusat',
                'phone' => '(021) 4244444',
            ],
            [
                'name' => 'Pengadilan Negeri Jakarta Selatan',
                'code' => 'PN.JKT.SEL',
                'address' => 'Jl. Ampera Raya No. 133, Ragunan, Pasar Minggu, Jakarta Selatan',
                'city' => 'Jakarta Selatan',
                'phone' => '(021) 7805555',
            ],
        ];

        foreach ($courts as $court) {
            Court::firstOrCreate(
                ['code' => $court['code']],
                $court
            );
        }
    }
}

