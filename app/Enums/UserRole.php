<?php

namespace App\Enums;

enum UserRole: string
{
    case InsuranceCompany = 'insurance';  // شركة التأمين — ترسل الملفات
    case LabAdmin = 'lab_admin';          // إدارة المعمل — تُسند الأطباء
    case Doctor = 'doctor';               // الطبيب — يراجع ويتصل

    public function label(): string
    {
        return match ($this) {
            self::InsuranceCompany => 'شركة تأمين',
            self::LabAdmin         => 'إدارة المعمل',
            self::Doctor           => 'طبيب',
        };
    }
}
