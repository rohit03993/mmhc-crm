<?php

namespace Database\Seeders;

use App\Models\Core\User;
use App\Modules\Academics\Models\AcademicExam;
use App\Modules\Academics\Models\AcademicExamQuestion;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\OsceSession;
use App\Modules\Academics\Models\OsceStation;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\Topic;
use App\Modules\Academics\Models\TopicResource;
use App\Modules\Profiles\Models\Profile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                'phone' => '0755-2745123',
                'address' => 'NH-12, Hoshangabad Road, Bhopal, Madhya Pradesh 462026, India',
                'is_active' => true,
            ]
        );

        $defaultLocation = \DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");

        // 2. Super Admin (academics)
        $superAdmin = User::firstOrCreate(
            ['email' => 'academic.super@themmhc.com'],
            [
                'name' => 'Vikram Joshi',
                'phone' => '9825511001',
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
                'phone' => '9825511002',
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
                'phone' => '9825511003',
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
                'phone' => '9825511004',
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
            ['email' => 'student1@medmiracle.com', 'name' => 'Kavita Sharma',     'phone' => '6200012001', 'uid' => 'ACAD-ST-001'],
            ['email' => 'student2@medmiracle.com', 'name' => 'Priyanka Patel',    'phone' => '6200012002', 'uid' => 'ACAD-ST-002'],
            ['email' => 'student3@medmiracle.com', 'name' => 'Anjali Yadav',      'phone' => '6200012003', 'uid' => 'ACAD-ST-003'],
            ['email' => 'student4@medmiracle.com', 'name' => 'Neha Gupta',       'phone' => '6200012004', 'uid' => 'ACAD-ST-004'],
            ['email' => 'student5@medmiracle.com', 'name' => 'Sneha Reddy',       'phone' => '6200012005', 'uid' => 'ACAD-ST-005'],
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
                ['sort_order' => $t['order'], 'is_completed' => false, 'teaching_method_keys' => null]
            );
            if ($t['name'] === 'Introduction to Human Anatomy' && empty($topic->teaching_method_keys)) {
                $topic->update(['teaching_method_keys' => ['demonstration', 'presentation', 'seminar']]);
            }
            $isSkeletal = $t['name'] === 'Skeletal System';
            if ($isSkeletal) {
                Assignment::where('topic_id', $topic->id)->where('title', 'Assignment: '.$t['name'])->delete();
                Assignment::updateOrCreate(
                    ['topic_id' => $topic->id, 'title' => 'Lab checklist: Bone lab safety & identification'],
                    [
                        'description' => 'Complete the checklist after your bone lab session.',
                        'due_date' => Carbon::now()->addDays(14),
                        'assignment_type' => Assignment::TYPE_CHECKLIST,
                        'checklist_items' => [
                            ['label' => 'Identify major bones on specimen', 'points' => 2],
                            ['label' => 'Dispose sharps correctly', 'points' => 1],
                            ['label' => 'Complete lab station cleanup', 'points' => 1],
                        ],
                        'assessment_type_keys' => ['checklist', 'practical', 'formative'],
                        'is_formative' => true,
                        'is_summative' => false,
                        'eval_includes_mcq' => false,
                        'eval_includes_practical' => true,
                        'eval_includes_viva' => false,
                        'eval_includes_checklist' => true,
                    ]
                );
            } else {
                Assignment::firstOrCreate(
                    ['topic_id' => $topic->id, 'title' => 'Assignment: '.$t['name']],
                    [
                        'description' => 'Submit your answers as per the guidelines.',
                        'due_date' => Carbon::now()->addDays(14),
                        'assignment_type' => Assignment::TYPE_FILE_UPLOAD,
                        'assessment_type_keys' => ['formative'],
                        'is_formative' => true,
                        'is_summative' => false,
                        'eval_includes_mcq' => $t['name'] === 'Introduction to Human Anatomy',
                        'eval_includes_practical' => false,
                        'eval_includes_viva' => false,
                        'eval_includes_checklist' => false,
                    ]
                );
            }
        }

        $fon = $subjects[2];
        $topicFon = Topic::firstOrCreate(
            ['subject_id' => $fon->id, 'name' => 'Bed Making and Patient Positioning'],
            ['sort_order' => 1, 'is_completed' => false, 'teaching_method_keys' => null]
        );
        if (empty($topicFon->teaching_method_keys)) {
            $topicFon->update(['teaching_method_keys' => ['lab_demo', 'skill_simulation', 'demonstration']]);
        }
        Assignment::firstOrCreate(
            ['topic_id' => $topicFon->id, 'title' => 'Practical: Bed Making'],
            [
                'description' => 'Submit your practical write-up with observations.',
                'due_date' => Carbon::now()->addDays(7),
            ]
        );

        // 11. Sample published MCQ exam (Anatomy cohort)
        $demoExam = AcademicExam::updateOrCreate(
            [
                'institution_id' => $institution->id,
                'title' => 'Demo: Anatomy warm-up (MCQ)',
            ],
            [
                'created_by' => $faculty2->id,
                'audience_type' => AcademicExam::AUDIENCE_SUBJECT_COHORT,
                'subject_id' => $anatomy->id,
                'batch_id' => null,
                'instructions' => 'Pick the best answer for each question.',
                'duration_minutes' => 20,
                'max_attempts' => 3,
                'shuffle_questions' => false,
                'shuffle_options' => false,
                'is_published' => true,
                'published_at' => now(),
                'opens_at' => now()->subDay(),
                'closes_at' => now()->addMonths(3),
                'allows_cross_institution' => false,
            ]
        );
        if ($demoExam->questions()->count() === 0) {
            $q1 = $demoExam->questions()->create([
                'body' => 'Which structure primarily protects the brain?',
                'question_type' => 'mcq_single',
                'sort_order' => 1,
                'points' => 1,
            ]);
            $q1->options()->createMany([
                ['label' => 'A', 'body' => 'Femur', 'is_correct' => false, 'sort_order' => 0],
                ['label' => 'B', 'body' => 'Cranium (skull)', 'is_correct' => true, 'sort_order' => 1],
                ['label' => 'C', 'body' => 'Radius', 'is_correct' => false, 'sort_order' => 2],
                ['label' => 'D', 'body' => 'Sternum', 'is_correct' => false, 'sort_order' => 3],
            ]);
            $q2 = $demoExam->questions()->create([
                'body' => 'The study of body structure is called:',
                'question_type' => 'mcq_single',
                'sort_order' => 2,
                'points' => 2,
            ]);
            $q2->options()->createMany([
                ['label' => 'A', 'body' => 'Physiology', 'is_correct' => false, 'sort_order' => 0],
                ['label' => 'B', 'body' => 'Anatomy', 'is_correct' => true, 'sort_order' => 1],
                ['label' => 'C', 'body' => 'Pathology', 'is_correct' => false, 'sort_order' => 2],
                ['label' => 'D', 'body' => 'Pharmacology', 'is_correct' => false, 'sort_order' => 3],
            ]);
        }

        $introTopic = Topic::where('subject_id', $anatomy->id)->where('name', 'Introduction to Human Anatomy')->first();
        if ($introTopic) {
            $introAssign = Assignment::where('topic_id', $introTopic->id)->first();
            if ($introAssign) {
                $introAssign->update([
                    'assignment_type' => Assignment::TYPE_QUIZ,
                    'assessment_type_keys' => ['quiz', 'formative', 'mcq_test_presentation'],
                    'eval_includes_mcq' => true,
                    'is_formative' => true,
                    'is_summative' => false,
                ]);
                $demoExam->update(['assignment_id' => $introAssign->id]);
            }
        }

        $introTopicForResource = Topic::where('subject_id', $anatomy->id)->where('name', 'Introduction to Human Anatomy')->first();
        if ($introTopicForResource && ! TopicResource::where('topic_id', $introTopicForResource->id)->exists()) {
            TopicResource::create([
                'topic_id' => $introTopicForResource->id,
                'title' => 'Demo: Human skeleton overview (video)',
                'description' => 'Replace this link with your institution’s preferred resource.',
                'resource_type' => TopicResource::TYPE_VIDEO_LINK,
                'video_url' => 'https://www.youtube.com/watch?v=WcHkD6MYVYw',
                'sort_order' => 0,
            ]);
        }

        $demoOsce = OsceSession::firstOrCreate(
            [
                'institution_id' => $institution->id,
                'title' => 'Demo: Year-1 nursing OSCE (sample)',
            ],
            [
                'batch_id' => $batch->id,
                'description' => "Practice layout — hand hygiene, vitals, communication.\nStudents: use station checklists during drills.",
                'starts_at' => Carbon::now()->addWeeks(2),
                'duration_minutes' => 90,
                'created_by' => $instAdmin->id,
            ]
        );
        if ($demoOsce->stations()->count() === 0) {
            OsceStation::create([
                'osce_session_id' => $demoOsce->id,
                'sort_order' => 0,
                'name' => 'Hand hygiene & PPE',
                'instructions' => 'Complete within time limit. Evaluator uses the checklist below.',
                'time_limit_seconds' => 300,
                'checklist_items' => [
                    ['label' => 'Hand wash / rub per protocol', 'points' => 1],
                    ['label' => 'Don PPE in correct order', 'points' => 2],
                    ['label' => 'Verbalize infection-control rationale', 'points' => 1],
                ],
            ]);
            OsceStation::create([
                'osce_session_id' => $demoOsce->id,
                'sort_order' => 1,
                'name' => 'Vital signs (demo station)',
                'instructions' => 'State normal ranges; demonstrate technique on manikin if available.',
                'time_limit_seconds' => 420,
                'checklist_items' => [
                    ['label' => 'Introduce self and explain procedure', 'points' => 1],
                    ['label' => 'Measure BP with correct cuff size', 'points' => 2],
                    ['label' => 'Record pulse & respiratory rate', 'points' => 1],
                ],
            ]);
        }

        if (! $demoExam->questions()->where('question_type', AcademicExamQuestion::TYPE_MCQ_MULTI)->exists()) {
            $nextOrder = (int) ($demoExam->questions()->max('sort_order') ?? 0) + 1;
            $q3 = $demoExam->questions()->create([
                'body' => 'Which of the following are major subdivisions of the nervous system? (Select all that apply.)',
                'explanation' => 'The nervous system is divided into the central nervous system (brain and spinal cord) and the peripheral nervous system (cranial and spinal nerves and ganglia).',
                'question_type' => AcademicExamQuestion::TYPE_MCQ_MULTI,
                'sort_order' => $nextOrder,
                'points' => 2,
            ]);
            $q3->options()->createMany([
                ['label' => 'A', 'body' => 'Central nervous system', 'is_correct' => true, 'sort_order' => 0],
                ['label' => 'B', 'body' => 'Peripheral nervous system', 'is_correct' => true, 'sort_order' => 1],
                ['label' => 'C', 'body' => 'Lymphatic system', 'is_correct' => false, 'sort_order' => 2],
                ['label' => 'D', 'body' => 'Endocrine system only', 'is_correct' => false, 'sort_order' => 3],
            ]);
        }

        $this->seedDemoProfileFieldsForAcademicUsers($institution);

        $this->command->info('Academic demo data seeded successfully.');
        $this->command->info('');
        $this->command->info('=== ACADEMICS LOGIN (use the same Login on main page) ===');
        $this->command->info('Super Admin:    academic.super@themmhc.com / '.$password);
        $this->command->info('Institution Admin: college.admin@medmiracle.com / '.$password);
        $this->command->info('Faculty 1:      faculty.nursing@medmiracle.com / '.$password);
        $this->command->info('Faculty 2:      faculty.anatomy@medmiracle.com / '.$password);
        $this->command->info('Student 1–5:    student1@medmiracle.com … student5@medmiracle.com / '.$password);
        $this->command->info('');
        $this->command->info('After login, academic users are redirected to /academics automatically.');
    }

    /**
     * Fill CRM profile fields so admin “profile completion” reflects more than name/phone alone.
     */
    protected function seedDemoProfileFieldsForAcademicUsers(Institution $institution): void
    {
        $demoAddress = $institution->address ?: 'NH-12, Hoshangabad Road, Bhopal, Madhya Pradesh 462026, India';

        $facultySeeds = [
            'faculty.nursing@medmiracle.com' => ['dob' => '1984-08-20', 'bio' => 'Demo faculty (Fundamentals of Nursing & community health).'],
            'faculty.anatomy@medmiracle.com' => ['dob' => '1986-04-12', 'bio' => 'Demo faculty (Anatomy & physiology).'],
        ];

        foreach ($facultySeeds as $email => $meta) {
            $u = User::where('email', $email)->first();
            if (! $u) {
                continue;
            }
            $u->update([
                'address' => $demoAddress,
                'date_of_birth' => Carbon::parse($meta['dob'])->startOfDay(),
            ]);
            Profile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'bio' => $meta['bio'],
                    'experience_years' => 10,
                    'specialization' => $u->qualification ?? 'Nursing',
                    'availability_status' => 'available',
                    'is_profile_complete' => false,
                ]
            );
        }

        $instAdmin = User::where('email', 'college.admin@medmiracle.com')->first();
        if ($instAdmin) {
            $instAdmin->update([
                'address' => $demoAddress,
                'date_of_birth' => Carbon::parse('1979-01-05')->startOfDay(),
            ]);
            Profile::updateOrCreate(
                ['user_id' => $instAdmin->id],
                [
                    'bio' => 'Demo institution administrator for MMHC College of Nursing.',
                    'experience_years' => 18,
                    'specialization' => 'Nursing administration',
                    'availability_status' => 'available',
                    'is_profile_complete' => false,
                ]
            );
        }
    }
}
