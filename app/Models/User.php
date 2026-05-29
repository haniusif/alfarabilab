<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'specialty', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'role'              => UserRole::class,
        'is_active'         => 'boolean',
    ];

    /** الملفات المُسندة لهذا الطبيب */
    public function assignedFiles(): HasMany
    {
        return $this->hasMany(PatientFile::class, 'doctor_id');
    }

    /** الملفات التي أرسلتها شركة التأمين هذه */
    public function sentFiles(): HasMany
    {
        return $this->hasMany(PatientFile::class, 'insurance_company_id');
    }

    public function isDoctor(): bool
    {
        return $this->role === UserRole::Doctor;
    }

    public function isLabAdmin(): bool
    {
        return $this->role === UserRole::LabAdmin;
    }

    public function isInsurance(): bool
    {
        return $this->role === UserRole::InsuranceCompany;
    }
}
