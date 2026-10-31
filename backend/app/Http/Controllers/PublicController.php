<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourtResource;
use App\Http\Resources\FaqResource;
use App\Models\Court;
use App\Models\Faq;
use App\Models\LegalCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    /**
     * Check divorce filing eligibility based on user criteria.
     * Accessible via GET /api/eligibility-check (supports query params and JSON body).
     */
    public function checkEligibility(Request $request): JsonResponse
    {
        $hasMarriageCertificate = filter_var($request->input('has_marriage_certificate', false), FILTER_VALIDATE_BOOLEAN);
        $hasLegalReason = filter_var($request->input('has_legal_reason', false), FILTER_VALIDATE_BOOLEAN);
        $isSeparated = filter_var($request->input('is_separated', false), FILTER_VALIDATE_BOOLEAN);
        $spouseAddressKnown = filter_var($request->input('spouse_address_known', true), FILTER_VALIDATE_BOOLEAN);

        $reasonsNotEligible = [];
        $recommendations = [];

        // 1. Validasi kepemilikan Buku / Akta Nikah
        if (! $hasMarriageCertificate) {
            $reasonsNotEligible[] = 'Buku Nikah (bagi Muslim) atau Akta Perkawinan (bagi Non-Muslim) merupakan bukti otentik syarat mutlak pengajuan perkara.';
            $recommendations[] = 'Jika buku nikah hilang atau rusak, urus Surat Duplikat Kutipan Akta Nikah terlebih dahulu di KUA (atau Kutipan Kedua di Disdukcapil).';
        }

        // 2. Validasi alasan hukum menurut UU Perkawinan / KHI
        if (! $hasLegalReason) {
            $reasonsNotEligible[] = 'Pengadilan mewajibkan adanya alasan perceraian yang sah (seperti perselisihan terus-menerus, penelantaran minimal 2 tahun berturut-turut, KDRT, atau salah satu pihak dihukum penjara).';
            $recommendations[] = 'Pastikan Anda telah memiliki kronologi kejadian dan alasan yang memenuhi ketentuan Pasal 19 PP No. 9/1975 atau Pasal 116 KHI.';
        }

        // Menentukan kelayakan akhir
        $isEligible = count($reasonsNotEligible) === 0;

        $nextSteps = [];
        if ($isEligible) {
            $nextSteps = [
                'Siapkan dokumen identitas (KTP pemohon dan fotokopi KK).',
                'Siapkan Buku Nikah / Akta Perkawinan asli beserta fotokopinya.',
                'Siapkan minimal 2 orang saksi keluarga/orang dekat yang mengetahui perselisihan.',
            ];

            if (! $spouseAddressKnown) {
                $recommendations[] = 'Karena alamat pasangan tidak diketahui (ghaib), perkara dapat diajukan melalui mekanisme Gugatan/Permohonan Ghaib dengan menyertakan Surat Keterangan Ghaib dari Kelurahan/Desa.';
            }

            if ($isSeparated) {
                $recommendations[] = 'Kondisi telah pisah rumah/ranjang memperkuat bukti adanya perselisihan yang tidak dapat dirukunkan kembali di persidangan.';
            }
        }

        return response()->json([
            'success' => true,
            'message' => $isEligible
                ? 'Anda memenuhi kriteria dasar untuk mengajukan perkara perceraian.'
                : 'Anda belum memenuhi seluruh kriteria dasar pengajuan perkara.',
            'data' => [
                'is_eligible' => $isEligible,
                'criteria_evaluated' => [
                    'has_marriage_certificate' => $hasMarriageCertificate,
                    'has_legal_reason' => $hasLegalReason,
                    'is_separated' => $isSeparated,
                    'spouse_address_known' => $spouseAddressKnown,
                ],
                'unmet_criteria' => $reasonsNotEligible,
                'recommendations' => $recommendations,
                'next_steps' => $nextSteps,
            ],
        ]);
    }

    /**
     * Get transparency statistics of registered cases.
     * Accessible via GET /api/statistics.
     */
    public function statistics(): JsonResponse
    {
        $totalCases = LegalCase::count();

        // Hitung total perkara berdasarkan status
        $statusCounts = LegalCase::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Klasifikasi status ke dalam kelompok transparansi yang ramah pengguna
        $summary = [
            'selesai' => ($statusCounts['selesai'] ?? 0) + ($statusCounts['approved'] ?? 0) + ($statusCounts['completed'] ?? 0),
            'diproses' => ($statusCounts['diproses'] ?? 0) + ($statusCounts['in_review'] ?? 0) + ($statusCounts['in_court'] ?? 0),
            'diajukan' => ($statusCounts['diajukan'] ?? 0) + ($statusCounts['submitted'] ?? 0),
            'draft' => $statusCounts['draft'] ?? 0,
            'ditolak' => ($statusCounts['ditolak'] ?? 0) + ($statusCounts['rejected'] ?? 0),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data statistik transparansi perkara.',
            'data' => [
                'total_cases' => $totalCases,
                'summary' => $summary,
                'by_raw_status' => $statusCounts,
                'total_courts' => Court::count(),
                'total_faqs' => Faq::where('is_published', true)->count(),
            ],
        ]);
    }

    /**
     * Get list of courts using CourtResource.
     * Accessible via GET /api/courts.
     */
    public function courts(Request $request): JsonResponse
    {
        $query = Court::query();

        // Fitur pencarian nama / kota / kode
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($city = $request->input('city')) {
            $query->where('city', 'like', "%{$city}%");
        }

        $courts = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengadilan berhasil dimuat.',
            'data' => CourtResource::collection($courts),
        ]);
    }

    /**
     * Get list of FAQs using FaqResource.
     * Accessible via GET /api/faqs.
     */
    public function faqs(Request $request): JsonResponse
    {
        $query = Faq::query()->where('is_published', true);

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->orderBy('order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar FAQ berhasil dimuat.',
            'data' => FaqResource::collection($faqs),
        ]);
    }
}

