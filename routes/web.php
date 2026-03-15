<?php

use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VidPull Routes
|--------------------------------------------------------------------------
*/

// ── Pages ─────────────────────────────────────────────────────────────────
Route::get('/', [DownloadController::class, 'index'])->name('home');

// ── API ───────────────────────────────────────────────────────────────────

// Fetch metadata for a URL
Route::post('/fetch-meta', [DownloadController::class, 'fetchMeta'])
    ->name('fetch-meta');

// Start a queued download
Route::post('/download', [DownloadController::class, 'startDownload'])
    ->name('download.start');

// SSE progress stream
Route::get('/download/progress/{uuid}', [DownloadController::class, 'progress'])
    ->name('download.progress')
    ->where('uuid', '[a-f0-9\-]{36}');

// Serve completed file
Route::get('/download/{uuid}/file', [DownloadController::class, 'serveFile'])
    ->name('download.file')
    ->where('uuid', '[a-f0-9\-]{36}');

// History JSON
Route::get('/history', [DownloadController::class, 'history'])
    ->name('history');

// Delete one history entry
Route::delete('/history/{uuid}', [DownloadController::class, 'deleteHistory'])
    ->name('history.delete')
    ->where('uuid', '[a-f0-9\-]{36}');
