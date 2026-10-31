<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UploadDocumentRequest;
use App\Http\Resources\CaseResource;
use App\Http\Resources\DocumentResource;
use App\Models\Court;
use App\Models\LegalCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CaseController extends Controller
{
    /**
     * Get list of cases owned by the authenticated citizen.
     */
    public function index(Request $request): JsonResponse
    {
        $cases = LegalCase::where('user_id', $request->user()->id)
            ->with(['court', 'spouseDetail', 'marriageDetail', 'documents', 'statusLogs'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar perkara berhasil dimuat.',
            'data' => CaseResource::collection($cases),
        ]);
    }

    /**
     * Get detail of a specific case owned by the authenticated citizen.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $case = LegalCase::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['court', 'spouseDetail', 'marriageDetail', 'documents', 'statusLogs'])
            ->first();

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Perkara tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail perkara berhasil dimuat.',
            'data' => new CaseResource($case),
        ]);
    }

    /**
     * Store a newly created case submission (Wizard Final / Submission).
     * Uses DB::transaction to ensure all tables (cases, spouse_details, marriage_details) are saved atomically.
     */
    public function store(StoreCaseRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $case = DB::transaction(function () use ($validated, $user) {
            $status = $validated['status'] ?? 'submitted';

            // Generate nomor referensi registrasi perkara
            $caseNumber = 'CL-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            // 1. Simpan ke tabel cases
            $legalCase = LegalCase::create([
                'user_id' => $user->id,
                'court_id' => $validated['court_id'],
                'case_number' => $caseNumber,
                'reason' => $validated['reason'],
                'status' => $status,
            ]);

            // 2. Simpan ke tabel spouse_details (One-to-One)
            $legalCase->spouseDetail()->create($validated['spouse_detail']);

            // 3. Simpan ke tabel marriage_details (One-to-One)
            $legalCase->marriageDetail()->create($validated['marriage_detail']);

            // 4. Catat riwayat log status awal di tabel case_status_logs
            $legalCase->statusLogs()->create([
                'status' => $status,
                'notes' => $status === 'draft'
                    ? 'Draf permohonan/gugatan perceraian disimpan oleh pemohon.'
                    : 'Pengajuan permohonan/gugatan perceraian berhasil disubmit oleh pemohon.',
                'changed_by_admin_id' => null,
            ]);

            return $legalCase->load(['court', 'spouseDetail', 'marriageDetail', 'statusLogs']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan perkara berhasil disimpan.',
            'data' => new CaseResource($case),
        ], 201);
    }

    /**
     * Upload document (KTP, Buku Nikah, etc.) securely to private local disk.
     */
    public function uploadDocument(UploadDocumentRequest $request, string $id): JsonResponse
    {
        $user = $request->user();

        // Pastikan perkara milik warga yang sedang login
        $case = LegalCase::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Perkara tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }

        $file = $request->file('file');

        // Simpan file ke disk local (PRIVAT, bukan public agar tidak bisa diakses langsung lewat URL web)
        $path = $file->store("documents/{$case->id}", 'local');

        // Simpan metadata ke tabel documents
        $document = $case->documents()->create([
            'file_path' => $path,
            'type' => $request->input('type'),
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diunggah secara aman.',
            'data' => new DocumentResource($document),
        ], 201);
    }

    /**
     * Calculate court cost estimation simulation based on court_id.
     */
    public function costEstimate(Request $request): JsonResponse
    {
        $request->validate([
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'radius' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $court = Court::findOrFail($request->input('court_id'));
        $radius = (int) $request->input('radius', 1);

        // Simulasi tarif radius panggilan sidang sesuai standar Panjar Biaya Perkara Pengadilan Agama
        $radiusRates = [
            1 => 80000,
            2 => 100000,
            3 => 125000,
            4 => 150000,
            5 => 200000,
        ];
        $ratePerCall = $radiusRates[$radius] ?? 80000;

        $pnbpRegistration = 30000;
        $atkProcess = 75000;
        $pemohonCalls = 3 * $ratePerCall; // 3x pemanggilan
        $termohonCalls = 4 * $ratePerCall; // 4x pemanggilan
        $redaksi = 10000;
        $materai = 10000;

        $totalEstimated = $pnbpRegistration + $atkProcess + $pemohonCalls + $termohonCalls + $redaksi + $materai;

        return response()->json([
            'success' => true,
            'message' => 'Simulasi taksiran panjar biaya perkara berhasil dihitung.',
            'data' => [
                'court' => [
                    'id' => $court->id,
                    'name' => $court->name,
                    'city' => $court->city,
                ],
                'radius_zone' => $radius,
                'breakdown' => [
                    'biaya_pendaftaran_pnbp' => $pnbpRegistration,
                    'biaya_proses_atk' => $atkProcess,
                    'panggilan_pemohon' => [
                        'frekuensi' => 3,
                        'tarif_per_panggilan' => $ratePerCall,
                        'subtotal' => $pemohonCalls,
                    ],
                    'panggilan_termohon' => [
                        'frekuensi' => 4,
                        'tarif_per_panggilan' => $ratePerCall,
                        'subtotal' => $termohonCalls,
                    ],
                    'biaya_redaksi' => $redaksi,
                    'meterai' => $materai,
                ],
                'total_estimate' => $totalEstimated,
                'total_estimate_formatted' => 'Rp ' . number_format($totalEstimated, 0, ',', '.'),
                'notes' => 'Nominal di atas merupakan estimasi panjar biaya perkara resmi (SK Ketua Pengadilan). Sisa panjar biaya yang tidak terpakai setelah putusan akan dikembalikan kepada pemohon.',
            ],
        ]);
    }
}

