<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('file_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_file_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('patient_file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_status_logs');
    }
};
