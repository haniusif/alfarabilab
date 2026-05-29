<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class LabInsuranceController extends Controller
{
    /** قائمة شركات التأمين مع عدد الملفات المُرسلة */
    public function index()
    {
        $companies = User::where('role', UserRole::InsuranceCompany->value)
            ->withCount('sentFiles')
            ->orderBy('name')
            ->get();

        return view('lab.insurance', ['companies' => $companies]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => UserRole::InsuranceCompany->value,
            'is_active' => true,
        ]);

        return back()->with('status', __('Insurance company :name added', ['name' => $data['name']]));
    }

    public function update(Request $request, User $company)
    {
        abort_unless($company->isInsurance(), 404);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($company->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $company->fill(['name' => $data['name'], 'email' => $data['email']]);

        if (! empty($data['password'])) {
            $company->password = Hash::make($data['password']);
        }

        $company->save();

        return back()->with('status', __('Insurance company :name updated', ['name' => $company->name]));
    }

    public function toggle(User $company)
    {
        abort_unless($company->isInsurance(), 404);

        $company->update(['is_active' => ! $company->is_active]);

        return back()->with('status', $company->is_active
            ? __('Insurance company :name activated', ['name' => $company->name])
            : __('Insurance company :name deactivated', ['name' => $company->name]));
    }
}
