<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Download a private case document securely.
     * Accessible only if the document's case belongs to the authenticated user.
     */
    public function download(Request $request, string $id): StreamedResponse|JsonResponse
    {
        $document = Document::with('case')->find($id);

        if (! $document) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan.',
            ], 404);
        }

        // Validasi Otorisasi: Dokumen HANYA bisa diunduh oleh pemilik perkara yang bersangkutan
        if (! $document->case || $document->case->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengunduh dokumen ini.',
            ], 403);
        }

        // Cek keberadaan berkas fisik di disk local (privat)
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

