<?php

use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\DoctorFileController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\LabAdminController;
use Illuminate\Support\Facades\Route;

// تسجيل الدخول للتطبيقات (إصدار رمز)
Route::post('login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('me', [AuthApiController::class, 'me']);
    Route::post('logout', [AuthApiController::class, 'logout']);


    // شركة التأمين — إرسال الملفات
    Route::middleware('role:insurance')->prefix('insurance')->group(function () {
        Route::post('files', [InsuranceController::class, 'submit']);
    });

    // إدارة المعمل — الإسناد للأطباء
    Route::middleware('role:lab_admin')->prefix('lab')->group(function () {
        Route::get('unassigned', [LabAdminController::class, 'unassigned']);
        Route::get('doctors', [LabAdminController::class, 'doctors']);
        Route::post('files/{file}/assign', [LabAdminController::class, 'assign']);
    });

    // الطبيب — الرفع والمتابعة
    Route::middleware('role:doctor')->prefix('doctor')->group(function () {
        Route::get('files', [DoctorFileController::class, 'index']);
        Route::post('files/upload', [DoctorFileController::class, 'upload']);
        Route::post('files/manual', [DoctorFileController::class, 'storeManual']);
        Route::get('files/{file}', [DoctorFileController::class, 'show']);
        Route::patch('files/{file}/status', [DoctorFileController::class, 'updateStatus']);
    });
});
