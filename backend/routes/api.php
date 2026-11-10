<?php

use App\Http\Controllers\Admin\CaseManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PublicController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Endpoints (Tanpa Auth)
Route::match(['get', 'post'], '/eligibility-check', [PublicController::class, 'checkEligibility']);
Route::get('/statistics', [PublicController::class, 'statistics']);
Route::get('/courts', [PublicController::class, 'courts']);
Route::get('/faqs', [PublicController::class, 'faqs']);
Route::get('/cases/cost-estimate', [CaseController::class, 'costEstimate']);

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Khusus Role Warga: Layanan Pengajuan Perkara Perceraian
    Route::middleware('role:warga')->group(function () {
        Route::get('/cases', [CaseController::class, 'index']);
        Route::post('/cases', [CaseController::class, 'store']);
        Route::post('/cases/{id}/submit', [CaseController::class, 'submit']);
        Route::get('/cases/{id}', [CaseController::class, 'show']);
        Route::post('/cases/{id}/documents', [CaseController::class, 'uploadDocument']);
        Route::get('/documents/{id}/download', [DocumentController::class, 'download']);
    });

    // Khusus Role Admin: Manajemen & Verifikasi Perkara Warga
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/cases', [CaseManagementController::class, 'index']);
        Route::get('/cases/{id}', [CaseManagementController::class, 'show']);
        Route::put('/cases/{id}/status', [CaseManagementController::class, 'updateStatus']);
        Route::get('/documents/{id}', [CaseManagementController::class, 'viewDocument']);
    });
});
