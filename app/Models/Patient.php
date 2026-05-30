<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Patient extends Model
{
    protected $fillable = [
        'name', 'mobile', 'membership_no', 'national_id',
        'guardian_id', 'is_head',
    ];

    protected $casts = [
        'is_head' => 'boolean',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(PatientFile::class);
    }

    /** ولي الأمر (إن كان هذا تابعاً) */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'guardian_id');
    }

    /** التوابع المباشرون (أبناء ولي الأمر) */
    public function dependents(): HasMany
    {
        return $this->hasMany(Patient::class, 'guardian_id');
    }

    /**
     * كل أفراد العائلة الذين يشتركون في نفس رقم الجوال (عدا هذا الفرد).
     * هذا هو جوهر ربط التوابع: الطبيب يشرح ملفاتهم في مكالمة واحدة.
     */
    public function familyMembers()
    {
        return static::where('mobile', $this->mobile)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * ترقية هذا الفرد إلى ولي أمر العائلة (الملف الرئيسي):
     * — يُعيّن هذا الفرد رأس العائلة، ويُجعل الباقون تابعين له.
     */
    public function promoteToHead(): void
    {
        DB::transaction(function () {
            static::where('mobile', $this->mobile)
                ->where('id', '!=', $this->id)
                ->update(['is_head' => false, 'guardian_id' => $this->id]);

            $this->update(['is_head' => true, 'guardian_id' => null]);
        });
    }

    /**
     * تسجيل مريض جديد مع ربطه تلقائياً بالعائلة عبر رقم الجوال.
     * أول من يُسجّل بالجوال يصبح ولي الأمر، والباقي توابع له.
     */
    public static function findOrCreateByMobile(array $data): self
    {
        $head = static::where('mobile', $data['mobile'])
            ->where('is_head', true)
            ->first();

        // إن كان نفس الشخص موجوداً مسبقاً (نفس الهوية) أعده
        $existing = static::where('national_id', $data['national_id'])->first();
        if ($existing) {
            return $existing;
        }

        return static::create([
            'name'          => $data['name'],
            'mobile'        => $data['mobile'],
            'membership_no' => $data['membership_no'],
            'national_id'   => $data['national_id'],
            'guardian_id'   => $head?->id,
            'is_head'       => $head === null, // ولي أمر إن لم يوجد رأس عائلة بهذا الجوال
        ]);
    }
}
