<?php

use App\Http\Controllers\PdfExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ----------------------------------------------------------------
// PDF Export Routes
// All are auth-gated; each controller method further checks the
// specific permission required.
// ----------------------------------------------------------------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/training/manual', [PdfExportController::class, 'trainingManual'])->name('training.manual');
});

Route::middleware(['auth', 'verified'])->prefix('pdf')->name('pdf.')->group(function () {

    Route::get('/hira/{hazard}',           [PdfExportController::class, 'hira'])
        ->name('hira');

    Route::get('/audit/{audit}',           [PdfExportController::class, 'auditReport'])
        ->name('audit');

    Route::get('/incident/{incident}',     [PdfExportController::class, 'incidentReport'])
        ->name('incident');

    Route::get('/ems/aspect/{aspect}',     [PdfExportController::class, 'environmentalAspect'])
        ->name('ems.aspect');

    Route::get('/esg/summary',             [PdfExportController::class, 'esgSummary'])
        ->name('esg.summary');

    Route::get('/esia/report/{report}',    [PdfExportController::class, 'esiaReport'])
        ->name('esia.report');

    Route::get('/hazop/study/{study}',     [PdfExportController::class, 'hazopStudy'])
        ->name('hazop.study');

    Route::get('/hazop/procedure',         [PdfExportController::class, 'hazopProcedure'])
        ->name('hazop.procedure');

    Route::get('/ptw/permit/{permit}',     [PdfExportController::class, 'ptwPermit'])
        ->name('ptw.permit');

    Route::get('/env/audit/{audit}',       [PdfExportController::class, 'environmentalAudit'])
        ->name('env.audit');

    Route::get('/ams/audit/{audit}',       [PdfExportController::class, 'amsAuditReport'])
        ->name('ams.audit');
});
