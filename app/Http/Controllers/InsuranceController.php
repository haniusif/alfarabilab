<?php

namespace App\Http\Controllers;

use App\Enums\FileStatus;
use App\Models\Patient;
use App\Models\PatientFile;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    /** شركة التأمين ترسل ملف مريض لإجراء فحوصات */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string'],
            'mobile'        => ['required', 'string'],
            'membership_no' => ['required', 'string'],
            'national_id'   => ['required', 'string'],
            'test_name'     => ['required', 'string'],
        ]);

        $patient = Patient::findOrCreateByMobile($data);

        $file = PatientFile::create([
            'patient_id'           => $patient->id,
            'insurance_company_id' => $request->user()->id,
            'test_name'            => $data['test_name'],
            'status'               => FileStatus::New->value,
        ]);

        return response()->json($file->load('patient'), 201);
    }
}
