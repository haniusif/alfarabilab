<?php

namespace Database\Seeders;

use App\Enums\FileStatus;
use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $insurance = User::create([
            'name' => 'بوبا العربية', 'email' => 'insurance@alfarabilab.test',
            'password' => Hash::make('password'), 'role' => UserRole::InsuranceCompany->value,
        ]);

        User::create([
            'name' => 'إدارة المعمل', 'email' => 'lab@alfarabilab.test',
            'password' => Hash::make('password'), 'role' => UserRole::LabAdmin->value,
        ]);

        $doctor = User::create([
            'name' => 'د محمداحمد بابكر', 'email' => 'doctor@alfarabilab.test',
            'password' => Hash::make('password'), 'role' => UserRole::Doctor->value,
            'specialty' => 'باطنية',
        ]);

        // أطباء تجريبيون إضافيون
        $extraDoctors = [
            ['د. سارة المالكي', 'doctor1@alfarabilab.test', 'باطنية'],
            ['د. خالد العتيبي', 'doctor2@alfarabilab.test', 'قلب'],
            ['د. منى الشهري', 'doctor3@alfarabilab.test', 'أطفال'],
            ['د. ياسر الحربي', 'doctor4@alfarabilab.test', 'مختبر'],
            ['د. ريم القرني', 'doctor5@alfarabilab.test', 'جلدية'],
        ];
        foreach ($extraDoctors as [$name, $email, $specialty]) {
            User::create([
                'name' => $name, 'email' => $email,
                'password' => Hash::make('password'), 'role' => UserRole::Doctor->value,
                'specialty' => $specialty,
            ]);
        }

        // عائلة واحدة بنفس رقم الجوال — الأب وابناه
        $father = Patient::create([
            'name' => 'محمد القحطاني', 'mobile' => '0551234567',
            'membership_no' => 'INS-44521', 'national_id' => '1098765432', 'is_head' => true,
        ]);

        $son = Patient::create([
            'name' => 'عبدالرحمن القحطاني', 'mobile' => '0551234567',
            'membership_no' => 'INS-44523', 'national_id' => '1298765434',
            'guardian_id' => $father->id, 'is_head' => false,
        ]);

        foreach ([$father, $son] as $p) {
            PatientFile::create([
                'patient_id' => $p->id,
                'insurance_company_id' => $insurance->id,
                'doctor_id' => $doctor->id,
                'test_name' => 'تحليل دم شامل',
                'result' => 'ضمن الطبيعي',
                'status' => FileStatus::Assigned->value,
            ]);
        }
    }
}
