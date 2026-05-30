<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('title', 50)->nullable()->after('name');
            $table->string('first_name', 100)->nullable()->after('title');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('avatar_path')->nullable()->after('specialty');
        });

        // تعبئة first_name / last_name من name الحالي (قبل أول مسافة = الاسم الأول)
        foreach (DB::table('users')->select('id', 'name')->get() as $user) {
            $name = trim((string) $user->name);
            if ($name === '') {
                continue;
            }
            $parts = preg_split('/\s+/', $name, 2);
            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parts[0] ?? null,
                'last_name'  => $parts[1] ?? null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['title', 'first_name', 'last_name', 'avatar_path']);
        });
    }
};
