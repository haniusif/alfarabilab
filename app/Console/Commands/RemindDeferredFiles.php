<?php

namespace App\Console\Commands;

use App\Models\PatientFile;
use Illuminate\Console\Command;

/**
 * يُذكّر الأطباء بالملفات المؤجلة التي حان موعدها.
 * يُجدول في routes/console.php ليعمل كل ساعة.
 */
class RemindDeferredFiles extends Command
{
    protected $signature = 'files:remind-deferred';
    protected $description = 'تنبيه الأطباء بالملفات المؤجلة التي حان موعدها';

    public function handle(): int
    {
        $due = PatientFile::dueToday()->with('doctor', 'patient')->get();

        foreach ($due as $file) {
            // هنا تُرسل الإشعار (Notification / واتساب / بريد) للطبيب
            // $file->doctor->notify(new DeferredFileDue($file));
            $this->info("تذكير: {$file->patient->name} — الطبيب {$file->doctor?->name}");
        }

        $this->info("إجمالي الملفات المستحقة: {$due->count()}");

        return self::SUCCESS;
    }
}
