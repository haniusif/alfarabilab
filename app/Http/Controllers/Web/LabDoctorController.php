<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class LabDoctorController extends Controller
{
    use ExportsCsv;

    /** قائمة الأطباء مع حِمل العمل */
    public function index()
    {
        $doctors = $this->doctorsWithLoad();

        return view('lab.doctors', ['doctors' => $doctors]);
    }

    /** تصدير تقرير حِمل الأطباء إلى CSV */
    public function export()
    {
        $headers = [__('Name'), __('Email'), __('Specialty'), __('Open files'), __('Total files'), __('Status')];

        $rows = $this->doctorsWithLoad()->map(fn (User $d) => [
            $d->name,
            $d->email,
            $d->specialty ?? '',
            $d->open_files_count,
            $d->total_files_count,
            $d->is_active ? __('Active') : __('Inactive'),
        ]);

        return $this->streamCsv('doctor-workload', $headers, $rows);
    }

    private function doctorsWithLoad()
    {
        return User::where('role', UserRole::Doctor->value)
            ->withCount([
                'assignedFiles as open_files_count' => fn ($q) => $q->open(),
                'assignedFiles as total_files_count',
            ])
            ->orderBy('name')
            ->get();
    }

    /** إضافة طبيب جديد */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'password'  => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'specialty' => $data['specialty'] ?? null,
            'password'  => Hash::make($data['password']),
            'role'      => UserRole::Doctor->value,
            'is_active' => true,
        ]);

        return back()->with('status', __('Doctor :name added', ['name' => $data['name']]));
    }

    /** تعديل بيانات طبيب */
    public function update(Request $request, User $doctor)
    {
        abort_unless($doctor->isDoctor(), 404);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($doctor->id)],
            'specialty' => ['nullable', 'string', 'max:255'],
            'password'  => ['nullable', 'string', 'min:6'],
        ]);

        $doctor->fill([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'specialty' => $data['specialty'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $doctor->password = Hash::make($data['password']);
        }

        $doctor->save();

        return back()->with('status', __('Doctor :name updated', ['name' => $doctor->name]));
    }

    /** تفعيل / تعطيل طبيب */
    public function toggle(User $doctor)
    {
        abort_unless($doctor->isDoctor(), 404);

        $doctor->update(['is_active' => ! $doctor->is_active]);

        return back()->with('status', $doctor->is_active
            ? __('Doctor :name activated', ['name' => $doctor->name])
            : __('Doctor :name deactivated', ['name' => $doctor->name]));
    }
}
