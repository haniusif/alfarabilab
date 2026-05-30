<?php

namespace App\Enums;

enum FileStatus: string
{
    case New = 'new';                 // وصل من شركة التأمين، لم يُسند بعد
    case Assigned = 'assigned';       // مُسند لطبيب
    case Explained = 'explained';     // تم الشرح (مغلق)
    case NoReply = 'no_reply';        // لم يتم الرد (يُعاد الاتصال)
    case Deferred = 'deferred';       // مؤجل لوقت محدد

    public function label(): string
    {
        return __(match ($this) {
            self::New       => 'New',
            self::Assigned  => 'Assigned',
            self::Explained => 'Explained',
            self::NoReply   => 'No reply',
            self::Deferred  => 'Deferred',
        });
    }

    /** هل الحالة نهائية (مغلقة)؟ */
    public function isClosed(): bool
    {
        return $this === self::Explained;
    }
}
