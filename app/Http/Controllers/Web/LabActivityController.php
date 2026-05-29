<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FileStatusLog;
use App\Models\PatientFile;

class LabActivityController extends Controller
{
    public function index()
    {
        // الملفات المتأخرة عن المهلة
        $overdue = PatientFile::breachingSla()
            ->with('patient', 'doctor', 'insuranceCompany')
            ->latest('updated_at')
            ->get();

        // سجل النشاط — أحدث التغييرات عبر كل الملفات
        $logs = FileStatusLog::with('user', 'file.patient')
            ->latest()
            ->paginate(25);

        return view('lab.activity', compact('overdue', 'logs'));
    }
}
