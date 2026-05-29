<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_company_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();

            // الحقول المستخرجة من تقرير المختبر (Laboratory Report)
            $table->string('patient_external_id')->nullable();  // Patient ID
            $table->string('referral_doctor')->nullable();      // Referral Doctor
            $table->string('age_gender')->nullable();           // Age/Gender
            $table->string('accession_no')->nullable();         // Accession No.
            $table->string('report_status')->nullable();        // Report Status
            $table->string('patient_ref_no')->nullable();       // Patient Ref. No.
            $table->string('test_name')->nullable();            // Test
            $table->string('result')->nullable();               // Result
            $table->string('unit')->nullable();                 // Unit
            $table->text('reference_range')->nullable();        // Reference Range

            // الملف الأصلي المرفوع (صورة واتساب أو PDF)
            $table->string('source_path')->nullable();
            $table->string('source_type')->nullable();          // image | pdf
            $table->json('raw_extraction')->nullable();         // ناتج Claude الخام للمراجعة

            // دورة الحالة
            $table->string('status')->default('new');           // FileStatus
            $table->text('doctor_notes')->nullable();
            $table->timestamp('deferred_to')->nullable();       // موعد إعادة الاتصال عند التأجيل
            $table->unsignedTinyInteger('call_attempts')->default(0);
            $table->timestamp('explained_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('doctor_id');
            $table->index('deferred_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_files');
    }
};
