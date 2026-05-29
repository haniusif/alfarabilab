<?php

namespace App\Models;

use App\Enums\FileStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class PatientFile extends Model
{
    protected $fillable = [
        'patient_id', 'insurance_company_id', 'doctor_id',
        'patient_external_id', 'referral_doctor', 'age_gender',
        'accession_no', 'report_status', 'patient_ref_no',
        'test_name', 'result', 'unit', 'reference_range',
        'source_path', 'source_type', 'raw_extraction',
        'status', 'doctor_notes', 'deferred_to', 'call_attempts', 'explained_at',
    ];

    protected $casts = [
        'status'         => FileStatus::class,
        'raw_extraction' => 'array',
        'deferred_to'    => 'datetime',
        'explained_at'   => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(User::class, 'insurance_company_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(FileStatusLog::class);
    }

    /* ---------- نطاقات الاستعلام ---------- */

    public function scopeForDoctor(Builder $q, int $doctorId): Builder
    {
        return $q->where('doctor_id', $doctorId);
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', FileStatus::Assigned->value);
    }

    /** المؤجلة التي حان موعدها اليوم أو فات */
    public function scopeDueToday(Builder $q): Builder
    {
        return $q->where('status', FileStatus::Deferred->value)
                 ->whereDate('deferred_to', '<=', now());
    }

    /* ---------- انتقالات الحالة ---------- */

    /** إسناد الملف لطبيب (المعمل) */
    public function assignTo(User $doctor, ?User $actor = null): void
    {
        $note = $this->doctor_id && $this->doctor_id !== $doctor->id
            ? "أُعيد الإسناد إلى {$doctor->name}"
            : "أُسند إلى {$doctor->name}";

        $this->changeStatus(FileStatus::Assigned, $actor, $note);
        $this->update(['doctor_id' => $doctor->id]);
    }

    /** سحب الإسناد وإعادة الملف لقائمة الانتظار (المعمل) */
    public function unassign(?User $actor = null): void
    {
        $previous = $this->doctor?->name;
        $this->changeStatus(FileStatus::New, $actor, $previous ? "سُحب الإسناد من {$previous}" : 'سُحب الإسناد');
        $this->update(['doctor_id' => null]);
    }

    /** عدد الملفات المفتوحة (غير المغلقة) المُسندة لطبيب */
    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('status', [
            FileStatus::Assigned->value,
            FileStatus::NoReply->value,
            FileStatus::Deferred->value,
        ]);
    }

    /** عدد الساعات قبل اعتبار الملف غير المُسند متأخراً */
    public const UNASSIGNED_SLA_HOURS = 24;

    /** الملفات المتأخرة: لم تُسند خلال المهلة، أو تأجيلها فات، أو تعذّر الرد مراراً */
    public function scopeBreachingSla(Builder $q): Builder
    {
        return $q->where(function (Builder $w) {
            $w->where(fn ($s) => $s->where('status', FileStatus::New->value)
                    ->where('created_at', '<=', now()->subHours(self::UNASSIGNED_SLA_HOURS)))
              ->orWhere(fn ($s) => $s->where('status', FileStatus::Deferred->value)
                    ->whereNotNull('deferred_to')->where('deferred_to', '<=', now()))
              ->orWhere(fn ($s) => $s->where('status', FileStatus::NoReply->value)
                    ->where('call_attempts', '>=', 3));
        });
    }

    /** سبب تأخر الملف (للعرض) — أو null إن لم يكن متأخراً */
    public function slaReason(): ?string
    {
        return match (true) {
            $this->status === FileStatus::New
                && $this->created_at?->lte(now()->subHours(self::UNASSIGNED_SLA_HOURS))
                    => __('Unassigned for over :h h', ['h' => self::UNASSIGNED_SLA_HOURS]),
            $this->status === FileStatus::Deferred
                && $this->deferred_to?->lte(now())
                    => __('Deferral date passed'),
            $this->status === FileStatus::NoReply && (int) $this->call_attempts >= 3
                    => __(':n call attempts, no reply', ['n' => $this->call_attempts]),
            default => null,
        };
    }

    public function markExplained(?User $actor = null, ?string $note = null): void
    {
        $this->update(['explained_at' => now()]);
        $this->changeStatus(FileStatus::Explained, $actor, $note);
    }

    public function markNoReply(?User $actor = null): void
    {
        $this->increment('call_attempts');
        $this->changeStatus(FileStatus::NoReply, $actor, 'لا يوجد رد');
    }

    public function defer(\DateTimeInterface $until, ?User $actor = null, ?string $reason = null): void
    {
        $this->update(['deferred_to' => $until]);
        $this->changeStatus(FileStatus::Deferred, $actor, $reason ?? "أُجّل إلى {$until->format('Y-m-d H:i')}");
    }

    protected function changeStatus(FileStatus $to, ?User $actor, ?string $note): void
    {
        $from = $this->status;
        $this->update(['status' => $to->value]);

        $this->statusLogs()->create([
            'user_id'     => $actor?->id,
            'from_status' => $from?->value,
            'to_status'   => $to->value,
            'note'        => $note,
        ]);
    }
}
