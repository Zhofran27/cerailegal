<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCaseStatusRequest;
use App\Http\Resources\CaseResource;
use App\Models\Document;
use App\Models\LegalCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaseManagementController extends Controller
{
    /**
     * Get all citizen case submissions with search and status filtering.
     * Accessible via GET /api/admin/cases.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LegalCase::with([
            'user',
            'court',
            'spouseDetail',
            'marriageDetail',
            'documents',
            'statusLogs',
        ])->latest();

        // 1. Filter berdasarkan status perkara
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // 2. Fitur Pencarian Cerdas (nomor perkara, nama/email pemohon, pasangan, pengadilan)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('spouseDetail', function ($sq) use ($search) {
                        $sq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    })
                    ->orWhereHas('court', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $cases = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar seluruh perkara warga berhasil dimuat.',
            'data' => CaseResource::collection($cases)->response()->getData(true),
        ]);
    }

    /**
     * Show detail of a citizen's case for admin verification.
     * Accessible via GET /api/admin/cases/{id}.
     */
    public function show(string $id): JsonResponse
    {
        $case = LegalCase::with([
            'user',
            'court',
            'spouseDetail',
            'marriageDetail',
            'documents',
            'statusLogs',
        ])->find($id);

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Perkara tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail perkara berhasil dimuat.',
            'data' => new CaseResource($case),
        ]);
    }

    /**
     * Update case status and automatically log change to case_status_logs.
     * Accessible via PUT /api/admin/cases/{id}/status.
     */
    public function updateStatus(UpdateCaseStatusRequest $request, string $id): JsonResponse
    {
        $case = LegalCase::find($id);

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Perkara tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validated();
        $admin = $request->user();

        // Menggunakan transaksi agar perubahan status dan pencatatan log bersifat atomik
        DB::transaction(function () use ($case, $validated, $admin) {
            $case->update([
                'status' => $validated['status'],
            ]);

            // Otomatis mencatat perubahan status ke tabel case_status_logs
            $case->statusLogs()->create([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? 'Status perkara diperbarui oleh admin.',
                'changed_by_admin_id' => $admin->id,
            ]);
        });

        $updatedCase = $case->fresh([
            'user',
            'court',
            'spouseDetail',
            'marriageDetail',
            'documents',
            'statusLogs',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status perkara berhasil diperbarui dan riwayat log telah dicatat.',
            'data' => new CaseResource($updatedCase),
        ]);
    }

    /**
     * View or download citizen document from private storage for verification purposes.
     * Accessible via GET /api/admin/documents/{id}.
     */
    public function viewDocument(string $id): StreamedResponse|JsonResponse
    {
        $document = Document::with('case.user')->find($id);

        if (! $document) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan.',
            ], 404);
        }

        // Cek keberadaan file fisik di disk local (privat)
        if (! Storage::disk('local')->exists($document->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas fisik tidak ditemukan di penyimpanan server.',
            ], 404);
        }

        // Sajikan berkas dari penyimpanan privat sebagai response download
        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }
}

