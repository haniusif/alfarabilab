<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            // المكوّنات الأساسية الأربعة لكل ملف
            $table->string('name');                 // الاسم
            $table->string('mobile');               // رقم الجوال (مفتاح ربط التوابع)
            $table->string('membership_no');        // رقم العضوية
            $table->string('national_id');          // رقم الهوية

            // التبعية: الأب وأبناؤه يشتركون في رقم الجوال.
            // ولي الأمر هو أول من سُجّل بهذا الجوال، والباقي يشيرون إليه.
            $table->foreignId('guardian_id')
                  ->nullable()
                  ->constrained('patients')
                  ->nullOnDelete();

            $table->boolean('is_head')->default(true); // هل هو ولي الأمر؟

            $table->timestamps();

            $table->index('mobile');        // البحث والتجميع برقم الجوال
            $table->index('membership_no');
            $table->index('national_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
