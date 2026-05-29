<?php

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DoctorWebController;
use App\Http\Controllers\Web\InsuranceWebController;
use App\Http\Controllers\Web\LabActivityController;
use App\Http\Controllers\Web\LabAnalyticsController;
use App\Http\Controllers\Web\LabDoctorController;
use App\Http\Controllers\Web\LabInsuranceController;
use App\Http\Controllers\Web\LabWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing', [
        'roles' => [
            ['M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21a.75.75 0 0 1 .75.75V21', 'Insurance company', "Sends the patient's file to the lab for tests."],
            ['M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.611L5 14.5', 'Lab administration', 'Receives incoming files and assigns them to a specialist doctor.'],
            ['M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75', 'Doctor', 'Reads the result, calls the patient, and updates the file status.'],
        ],
        'steps' => [
            ['Insurance sends the file', 'A patient file arrives from the insurer.'],
            ['The lab assigns a doctor', 'An incoming file is routed to a specialist.'],
            ['The system reads the report', 'OCR extracts the fields automatically.'],
            ['The doctor calls and updates', 'Status is updated after the call.'],
        ],
        'features' => [
            ['M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z', 'Automatic reading', 'Images or PDFs become structured tasks in seconds.'],
            ['M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z', 'Family linking', 'Members sharing a mobile number are grouped for one call.'],
            ['M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z', 'Status tracking', 'Deferred, no-reply, explained — nothing slips.'],
            ['M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75', 'Duplicate detection', 'Re-uploaded reports are flagged by accession number.'],
            ['M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418', 'Arabic & English', 'Full RTL and LTR with light and dark modes.'],
            ['M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z', 'Privacy-first', 'Reports can be read on-device — no data leaves.'],
        ],
    ]);
})->name('landing');

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, \App\Http\Middleware\SetLocale::SUPPORTED, true)) {
        session(['locale' => $locale]);
    }

    return redirect()->back(fallback: '/');
})->name('locale.switch');

Route::get('/login', [AuthWebController::class, 'show'])->name('login');
Route::post('/login', [AuthWebController::class, 'login']);
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:insurance')->group(function () {
        Route::get('/insurance', [InsuranceWebController::class, 'index'])->name('insurance.index');
        Route::get('/insurance/files/export', [InsuranceWebController::class, 'export'])->name('insurance.export');
        Route::get('/insurance/files/{file}', [InsuranceWebController::class, 'show'])->name('insurance.show');
        Route::post('/insurance/files', [InsuranceWebController::class, 'store'])->name('insurance.store');
    });

    Route::middleware('role:lab_admin')->group(function () {
        Route::get('/lab', [LabWebController::class, 'index'])->name('lab.index');
        Route::post('/lab/upload', [LabWebController::class, 'upload'])->name('lab.upload');
        Route::get('/lab/files', [LabWebController::class, 'files'])->name('lab.files');
        Route::get('/lab/files/export', [LabWebController::class, 'export'])->name('lab.files.export');
        Route::post('/lab/files/bulk-assign', [LabWebController::class, 'bulkAssign'])->name('lab.bulk-assign');
        Route::post('/lab/files/auto-assign', [LabWebController::class, 'autoAssign'])->name('lab.auto-assign');
        Route::post('/lab/files/{file}/assign', [LabWebController::class, 'assign'])->name('lab.assign');
        Route::post('/lab/files/{file}/unassign', [LabWebController::class, 'unassign'])->name('lab.unassign');

        Route::get('/lab/doctors', [LabDoctorController::class, 'index'])->name('lab.doctors');
        Route::get('/lab/doctors/export', [LabDoctorController::class, 'export'])->name('lab.doctors.export');
        Route::post('/lab/doctors', [LabDoctorController::class, 'store'])->name('lab.doctors.store');
        Route::patch('/lab/doctors/{doctor}', [LabDoctorController::class, 'update'])->name('lab.doctors.update');
        Route::patch('/lab/doctors/{doctor}/toggle', [LabDoctorController::class, 'toggle'])->name('lab.doctors.toggle');

        Route::get('/lab/insurers', [LabInsuranceController::class, 'index'])->name('lab.insurers');
        Route::post('/lab/insurers', [LabInsuranceController::class, 'store'])->name('lab.insurers.store');
        Route::patch('/lab/insurers/{company}', [LabInsuranceController::class, 'update'])->name('lab.insurers.update');
        Route::patch('/lab/insurers/{company}/toggle', [LabInsuranceController::class, 'toggle'])->name('lab.insurers.toggle');

        Route::get('/lab/analytics', [LabAnalyticsController::class, 'index'])->name('lab.analytics');
        Route::get('/lab/activity', [LabActivityController::class, 'index'])->name('lab.activity');
    });

    Route::middleware('role:doctor')->prefix('doctor')->group(function () {
        Route::get('/', [DoctorWebController::class, 'index'])->name('doctor.index');
        Route::get('/files', [DoctorWebController::class, 'files'])->name('doctor.files');
        Route::get('/duplicates', [DoctorWebController::class, 'duplicates'])->name('doctor.duplicates');
        Route::post('/files/upload', [DoctorWebController::class, 'upload'])->name('doctor.upload');
        Route::post('/files/manual', [DoctorWebController::class, 'manual'])->name('doctor.manual');
        Route::get('/files/{file}', [DoctorWebController::class, 'show'])->name('doctor.show');
        Route::get('/files/{file}/source', [DoctorWebController::class, 'source'])->name('doctor.source');
        Route::patch('/files/{file}/status', [DoctorWebController::class, 'status'])->name('doctor.status');
        Route::delete('/files/{file}', [DoctorWebController::class, 'destroy'])->name('doctor.destroy');
    });
});
