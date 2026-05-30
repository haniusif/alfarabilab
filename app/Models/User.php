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
        'name', 'title', 'first_name', 'last_name',
        'email', 'password', 'role', 'specialty', 'is_active', 'avatar_path',
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

    /** يجمع اللقب + الاسم الأول + الأخير في سلسلة عرض */
    public static function composeName(?string $title, ?string $first, ?string $last): string
    {
        return trim(implode(' ', array_filter([
            trim((string) $title),
            trim((string) $first),
            trim((string) $last),
        ], fn ($p) => $p !== '')));
    }

    /** رابط عام للصورة الشخصية (أو placeholder) */
    public function avatarUrl(): ?string
    {
        return $this->avatar_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar_path)
            : null;
    }
}
