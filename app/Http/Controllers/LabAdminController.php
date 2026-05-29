<?php

namespace App\Http\Controllers;

use App\Enums\FileStatus;
use App\Enums\UserRole;
use App\Models\PatientFile;
use App\Models\User;
use Illuminate\Http\Request;

class LabAdminController extends Controller
{
    /** الملفات الواردة من شركات التأمين بانتظار الإسناد */
    public function unassigned()
    {
        return response()->json(
            PatientFile::where('status', FileStatus::New->value)
                ->whereNull('doctor_id')
                ->with('patient', 'insuranceCompany')
                ->get()
        );
    }

    /** إسناد ملف لطبيب */
    public function assign(Request $request, PatientFile $file)
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
        ]);

        $doctor = User::findOrFail($data['doctor_id']);
        abort_unless($doctor->isDoctor(), 422, 'المستخدم المحدد ليس طبيباً');

        $file->assignTo($doctor, $request->user());

        return response()->json($file->fresh()->load('doctor'));
    }

    public function doctors()
    {
        return response()->json(
            User::where('role', UserRole::Doctor->value)
                ->where('is_active', true)
                ->get(['id', 'name', 'specialty'])
        );
    }
}
