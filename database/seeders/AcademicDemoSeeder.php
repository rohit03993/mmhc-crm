<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Core\User;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\Topic;
use App\Modules\Academics\Models\Assignment;
use Carbon\Carbon;

/**
 * Seeds demo data for Academics module (nursing college – India).
 * Run: php artisan db:seed --class=AcademicDemoSeeder
 */
class AcademicDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'password123';

        // 1. Institution
        $institution = Institution::firstOrCreate(
            ['code' => 'MMCN-BPL'],
            [
                'name' => 'MeD Miracle College of Nursing',
                'email' => 'academics@medmiracle.com',
                'phone' => '0755-1234567',
                'address' => 'Bhopal, Madhya Pradesh, India',
                'is_active' => true,
            ]
        );

        $defaultLocation = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");

        // 2. Super Admin (academics)
        $superAdmin = User::firstOrCreate(
            ['email' => 'academic.super@themmhc.com'],
            [
                'name' => 'Academic Super Admin',
                'phone' => '9111111001',
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'unique_id' => 'ACAD-SA-001',
                'is_active' => true,
                'email_verified_at' => now(),
                'location' => $defaultLocation,
            ]
        );
        DB::table('users')->where('id', $superAdmin->id)->update(['password' => Hash::make($password)]);

        // 3. Institution Admin
        $instAdmin = User::firstOrCreate(
            ['email' => 'college.admin@medmiracle.com'],
            [
                'name' => 'Dr. Sunita Verma',
                'phone' => '9111111002',
                'password' => Hash::make($password),
                'role' => 'institution_admin',
                'unique_id' => 'ACAD-IA-001',
                'academic_institution_id' => $institution->id,
                'is_active' => true,
                'email_verified_at' => now(),
                'location' => $defaultLocation,
            ]
        );
        $instAdmin->update(['academic_institution_id' => $institution->id]);
        DB::table('users')->where('id', $instAdmin->id)->update(['password' => Hash::make($password)]);

        // 4. Faculty
        $faculty1 = User::firstOrCreate(
            ['email' => 'faculty.nursing@medmiracle.com'],
            [
                'name' => 'Prof. Rajesh Kumar',
                'phone' => '9111111003',
                'password' => Hash::make($password),
                'role' => 'faculty',
                'unique_id' => 'ACAD-F-001',
                'academic_institution_id' => $institution->id,
                'qualification' => 'M.Sc Nursing',
                'is_active' => true,
                'email_verified_at' => now(),
                'location' => $defaultLocation,
            ]
        );
        $faculty1->update(['academic_institution_id' => $institution->id]);
        DB::table('users')->where('id', $faculty1->id)->update(['password' => Hash::make($password)]);

        $faculty2 = User::firstOrCreate(
            ['email' => 'faculty.anatomy@medmiracle.com'],
            [
                'name' => 'Dr. Anjali Singh',
                'phone' => '9111111004',
                'password' => Hash::make($password),
                'role' => 'faculty',
                'unique_id' => 'ACAD-F-002',
                'academic_institution_id' => $institution->id,
                'qualification' => 'M.Sc Anatomy',
                'is_active' => true,
                'email_verified_at' => now(),
                'location' => $defaultLocation,
            ]
        );
        $faculty2->update(['academic_institution_id' => $institution->id]);
        DB::table('users')->where('id', $faculty2->id)->update(['password' => Hash::make($password)]);

        // 5. Students (B.Sc Nursing – India style names)
        $students = [];
        $studentData = [
            ['email' => 'student1@medmiracle.com', 'name' => 'Kavita Sharma',     'phone' => '9111112001', 'uid' => 'ACAD-ST-001'],
            ['email' => 'student2@medmiracle.com', 'name' => 'Priyanka Patel',    'phone' => '9111112002', 'uid' => 'ACAD-ST-002'],
            ['email' => 'student3@medmiracle.com', 'name' => 'Anjali Yadav',      'phone' => '9111112003', 'uid' => 'ACAD-ST-003'],
            ['email' => 'student4@medmiracle.com', 'name' => 'Neha Gupta',       'phone' => '9111112004', 'uid' => 'ACAD-ST-004'],
            ['email' => 'student5@medmiracle.com', 'name' => 'Sneha Reddy',       'phone' => '9111112005', 'uid' => 'ACAD-ST-005'],
        ];
        foreach ($studentData as $s) {
            $user = User::firstOrCreate(
                ['email' => $s['email']],
                [
                    'name' => $s['name'],
                    'phone' => $s['phone'],
                    'password' => Hash::make($password),
                    'role' => 'student',
                    'unique_id' => $s['uid'],
                    'academic_institution_id' => $institution->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'location' => $defaultLocation,
                ]
            );
            $user->update(['academic_institution_id' => $institution->id]);
            DB::table('users')->where('id', $user->id)->update(['password' => Hash::make($password)]);
            $students[] = $user;
        }

        // 6. Batch
        $batch = Batch::firstOrCreate(
            [
                'institution_id' => $institution->id,
                'name' => 'B.Sc Nursing 2024-25',
            ],
            [
                'academic_year' => '2024-25',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2028-06-30'),
                'is_active' => true,
            ]
        );

        // 7. Assign students and faculty to batch
        $batch->students()->sync(collect($students)->pluck('id')->mapWithKeys(fn ($id) => [$id => ['type' => 'student']])->toArray());
        $batch->faculty()->sync([$faculty1->id => ['type' => 'faculty'], $faculty2->id => ['type' => 'faculty']]);

        // 8. Subjects (common in Indian B.Sc Nursing)
        $subjects = [];
        $subjectList = [
            ['name' => 'Anatomy', 'code' => 'ANAT-101'],
            ['name' => 'Physiology', 'code' => 'PHYS-101'],
            ['name' => 'Fundamentals of Nursing', 'code' => 'FON-101'],
            ['name' => 'Community Health Nursing', 'code' => 'CHN-101'],
        ];
        foreach ($subjectList as $sub) {
            $subject = Subject::firstOrCreate(
                [
                    'batch_id' => $batch->id,
                    'name' => $sub['name'],
                ],
                ['code' => $sub['code'], 'is_active' => true]
            );
            $subjects[] = $subject;
        }

        // 9. Assign faculty to subjects
        $subjects[0]->faculty()->sync([$faculty2->id]); // Anatomy -> Dr. Anjali
        $subjects[1]->faculty()->sync([$faculty2->id]); // Physiology -> Dr. Anjali
        $subjects[2]->faculty()->sync([$faculty1->id]); // FON -> Prof. Rajesh
        $subjects[3]->faculty()->sync([$faculty1->id]); // CHN -> Prof. Rajesh

        // 10. Topics and Assignments (sample)
        $anatomy = $subjects[0];
        $topicsAnatomy = [
            ['name' => 'Introduction to Human Anatomy', 'order' => 1],
            ['name' => 'Skeletal System', 'order' => 2],
            ['name' => 'Cardiovascular System', 'order' => 3],
        ];
        foreach ($topicsAnatomy as $t) {
            $topic = Topic::firstOrCreate(
                ['subject_id' => $anatomy->id, 'name' => $t['name']],
                ['sort_order' => $t['order'], 'is_completed' => false]
            );
            Assignment::firstOrCreate(
                ['topic_id' => $topic->id, 'title' => 'Assignment: ' . $t['name']],
                [
                    'description' => 'Submit your answers as per the guidelines.',
                    'due_date' => Carbon::now()->addDays(14),
                ]
            );
        }

        $fon = $subjects[2];
        $topicFon = Topic::firstOrCreate(
            ['subject_id' => $fon->id, 'name' => 'Bed Making and Patient Positioning'],
            ['sort_order' => 1, 'is_completed' => false]
        );
        Assignment::firstOrCreate(
            ['topic_id' => $topicFon->id, 'title' => 'Practical: Bed Making'],
            [
                'description' => 'Submit your practical write-up with observations.',
                'due_date' => Carbon::now()->addDays(7),
            ]
        );

        $this->command->info('Academic demo data seeded successfully.');
        $this->command->info('');
        $this->command->info('=== ACADEMICS LOGIN (use the same Login on main page) ===');
        $this->command->info('Super Admin:    academic.super@themmhc.com / ' . $password);
        $this->command->info('Institution Admin: college.admin@medmiracle.com / ' . $password);
        $this->command->info('Faculty 1:      faculty.nursing@medmiracle.com / ' . $password);
        $this->command->info('Faculty 2:      faculty.anatomy@medmiracle.com / ' . $password);
        $this->command->info('Student 1–5:    student1@medmiracle.com … student5@medmiracle.com / ' . $password);
        $this->command->info('');
        $this->command->info('After login, academic users are redirected to /academics automatically.');
    }
}
