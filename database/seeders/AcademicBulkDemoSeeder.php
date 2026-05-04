<?php

namespace Database\Seeders;

use App\Models\Core\User;
use App\Modules\Academics\Models\AcademicExam;
use App\Modules\Academics\Models\AcademicExamAttempt;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Models\Topic;
use App\Modules\Profiles\Models\Document;
use App\Modules\Profiles\Models\Profile;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Large academics demo: 15 nursing/paramedical-style colleges, each with
 * 1 institution admin, 6 faculty, 18 students (2 batches × 9), subjects,
 * topics, homework (assignments), published quizzes, partial submissions, and
 * rich student/faculty profiles (avatar + documents).
 *
 * Run: php artisan db:seed --class=AcademicBulkDemoSeeder
 * Password for all bulk demo logins: same as {@see AcademicBulkDemoSeeder::DEMO_PASSWORD}.
 */
class AcademicBulkDemoSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'password123';

    private const INSTITUTION_COUNT = 15;

    private const FACULTY_PER_COLLEGE = 6;

    private const STUDENTS_PER_COLLEGE = 18;

    /** 1×1 transparent PNG */
    private const PNG_BYTES = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    /** Minimal valid PDF (single empty page) */
    private const PDF_BYTES_BASE64 = 'JVBERi0xLjQKJeLjz9MKMSAwIG9iago8PC9UeXBlL0NhdGFsb2cvUGFnZXMgMiAwIFI+PgplbmRvYmoKMiAwIG9iago8PC9UeXBlL1BhZ2VzL0tpZHNbMyAwIFJdL0NvdW50IDE+PgplbmRvYmoKMyAwIG9iago8PC9UeXBlL1BhZ2UvTWVkaWFCb3hbMCAwIDIwIDIwXT4+CmVuZG9iagp4cmVmCjAgNAowMDAwMDAwMDAwIDY1NTM1IGYgCjAwMDAwMDAwMTAgMDAwMDAgbiAKMDAwMDAwMDA3OSAwMDAwMCBuIAowMDAwMDAwMTQzIDAwMDAwIG4gCnRyYWlsZXIKPDwvU2l6ZSA0L1Jvb3QgMSAwIFI+PgpzdGFydHhyZWYKMTk1CiUlRU9G';

    /** @var list<array{name: string, city: string, code_suffix: string}> */
    private array $collegeBlueprints = [
        ['name' => 'St. Agnes College of Nursing', 'city' => 'Kochi, Kerala', 'code_suffix' => 'AGN'],
        ['name' => 'Rajendra Institute Nursing Sciences', 'city' => 'Ranchi, Jharkhand', 'code_suffix' => 'RIN'],
        ['name' => 'CMC Vellore School of Nursing', 'city' => 'Vellore, Tamil Nadu', 'code_suffix' => 'CMC'],
        ['name' => 'SNDT College of Nursing', 'city' => 'Mumbai, Maharashtra', 'code_suffix' => 'SND'],
        ['name' => 'AIIMS Delhi College of Nursing', 'city' => 'New Delhi', 'code_suffix' => 'AID'],
        ['name' => 'PGIMER Chandigarh Nursing Wing', 'city' => 'Chandigarh', 'code_suffix' => 'PGI'],
        ['name' => 'Manipal College of Nursing', 'city' => 'Manipal, Karnataka', 'code_suffix' => 'MNP'],
        ['name' => 'Apollo College of Nursing', 'city' => 'Chennai, Tamil Nadu', 'code_suffix' => 'APL'],
        ['name' => 'Bharati Vidyapeeth Nursing College', 'city' => 'Pune, Maharashtra', 'code_suffix' => 'BVP'],
        ['name' => 'Government College of Nursing', 'city' => 'Thiruvananthapuram, Kerala', 'code_suffix' => 'GCN'],
        ['name' => 'Nightingale School of Nursing', 'city' => 'Kolkata, West Bengal', 'code_suffix' => 'NGT'],
        ['name' => 'Ramaiah Institute of Nursing', 'city' => 'Bengaluru, Karnataka', 'code_suffix' => 'RMS'],
        ['name' => 'KLE Academy of Nursing', 'city' => 'Belagavi, Karnataka', 'code_suffix' => 'KLE'],
        ['name' => 'Sterling College of Nursing', 'city' => 'Vadodara, Gujarat', 'code_suffix' => 'STL'],
        ['name' => 'MMHC Regional Nursing Institute', 'city' => 'Indore, Madhya Pradesh', 'code_suffix' => 'MMR'],
    ];

    /** @var list<string> */
    private array $subjectPool = [
        'Anatomy & Physiology',
        'Fundamentals of Nursing',
        'Medical-Surgical Nursing',
        'Community Health Nursing',
        'Pediatric Nursing',
        'Mental Health Nursing',
        'Pharmacology',
        'Nutrition & Biochemistry',
        'Pathology',
        'Microbiology',
    ];

    private int $globalUidSeq = 700000;

    private int $demoMobileSeq = 6200013000;

    private ?FakerGenerator $inFaker = null;

    /**
     * Indian-locale Faker for realistic demo names and addresses.
     */
    private function indianFaker(): FakerGenerator
    {
        return $this->inFaker ??= FakerFactory::create('en_IN');
    }

    /** Next unique 10-digit Indian mobile number (starts with 6–9). */
    private function nextIndianMobile(): string
    {
        $n = $this->demoMobileSeq++;
        if ($this->demoMobileSeq > 9999999999) {
            $this->demoMobileSeq = 6200013000;
        }

        return (string) $n;
    }

    public function run(): void
    {
        // Let User "password" hashed cast hash once (avoid double-hash from pre-hashed values)
        $password = self::DEMO_PASSWORD;
        $defaultLocation = DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
        $png = base64_decode(self::PNG_BYTES, true) ?: '';
        $pdf = base64_decode(self::PDF_BYTES_BASE64, true) ?: '';

        $this->command?->info('Academic bulk demo: seeding '.self::INSTITUTION_COUNT.' colleges (this may take 1–3 minutes)…');

        for ($c = 1; $c <= self::INSTITUTION_COUNT; $c++) {
            $bp = $this->collegeBlueprints[$c - 1];
            $code = 'BULK-'.$bp['code_suffix'].'-'.str_pad((string) $c, 2, '0', STR_PAD_LEFT);

            $institution = Institution::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $bp['name'].' (Demo '.$c.')',
                    'email' => 'office+'.$code.'@academic-bulk.demo',
                    'phone' => '1800'.str_pad((string) (400 + $c), 7, '0'),
                    'address' => $bp['city'],
                    'is_active' => true,
                ]
            );

            $admin = $this->makeUser([
                'email' => strtolower($code).'-admin@academic-bulk.demo',
                'name' => $this->indianFaker()->name(),
                'role' => 'institution_admin',
                'unique_id' => 'B-'.$bp['code_suffix'].'-IA',
                'academic_institution_id' => $institution->id,
                'password' => $password,
                'location' => $defaultLocation,
            ]);

            $facultyIds = [];
            for ($f = 1; $f <= self::FACULTY_PER_COLLEGE; $f++) {
                $facultyIds[] = $this->makeUser([
                    'email' => strtolower($code).'-f'.str_pad((string) $f, 2, '0', STR_PAD_LEFT).'@academic-bulk.demo',
                    'name' => 'Dr. '.$this->indianFaker()->firstName().' '.$this->indianFaker()->lastName(),
                    'role' => 'faculty',
                    'unique_id' => 'B-'.$bp['code_suffix'].'-F'.str_pad((string) $f, 2, '0', STR_PAD_LEFT),
                    'academic_institution_id' => $institution->id,
                    'qualification' => fake()->randomElement(['M.Sc Nursing', 'B.Sc Nursing', 'Ph.D Nursing', 'M.Sc Anatomy']),
                    'password' => $password,
                    'location' => $defaultLocation,
                ])->id;
            }

            $studentIds = [];
            for ($s = 1; $s <= self::STUDENTS_PER_COLLEGE; $s++) {
                $studentIds[] = $this->makeUser([
                    'email' => strtolower($code).'-s'.str_pad((string) $s, 3, '0', STR_PAD_LEFT).'@academic-bulk.demo',
                    'name' => $this->indianFaker()->name(),
                    'role' => 'student',
                    'unique_id' => 'B-'.$bp['code_suffix'].'-S'.str_pad((string) $s, 3, '0', STR_PAD_LEFT),
                    'academic_institution_id' => $institution->id,
                    'password' => $password,
                    'location' => $defaultLocation,
                ])->id;
            }

            $batchNames = [
                'B.Sc Nursing – Year '.fake()->numberBetween(1, 4),
                fake()->randomElement(['GNM – Year 1', 'PB B.Sc Nursing – Year 1', 'M.Sc Nursing – Year 1', 'Diploma in Nursing – Year 2']),
            ];
            $batches = [];
            foreach ($batchNames as $bn) {
                $batches[] = Batch::firstOrCreate(
                    ['institution_id' => $institution->id, 'name' => $bn.' ('.$code.')'],
                    [
                        'academic_year' => '2025-26',
                        'start_date' => Carbon::parse('2025-07-01'),
                        'end_date' => Carbon::parse('2029-06-30'),
                        'is_active' => true,
                    ]
                );
            }

            $batch1Students = array_slice($studentIds, 0, 9);
            $batch2Students = array_slice($studentIds, 9, 9);
            $batches[0]->students()->sync(array_fill_keys($batch1Students, ['type' => 'student']));
            $batches[1]->students()->sync(array_fill_keys($batch2Students, ['type' => 'student']));
            $facultyPivot = array_fill_keys($facultyIds, ['type' => 'faculty']);
            $batches[0]->faculty()->sync($facultyPivot);
            $batches[1]->faculty()->sync($facultyPivot);

            $allSubjects = [];
            foreach ($batches as $bi => $batch) {
                $offset = ($c + $bi) % 5;
                for ($si = 0; $si < 5; $si++) {
                    $subName = $this->subjectPool[($offset + $si) % count($this->subjectPool)];
                    $subject = Subject::firstOrCreate(
                        ['batch_id' => $batch->id, 'name' => $subName],
                        [
                            'code' => 'S'.$batch->id.'-'.$si.'-'.substr(md5($subName), 0, 6),
                            'is_active' => true,
                        ]
                    );
                    $fid = $facultyIds[($si + $bi) % self::FACULTY_PER_COLLEGE];
                    $subject->faculty()->syncWithoutDetaching([$fid]);
                    $allSubjects[] = ['batch' => $batch, 'subject' => $subject, 'faculty_id' => $fid];
                }
            }

            $assignmentsForSubmissions = [];

            foreach ($allSubjects as $idx => $row) {
                $subject = $row['subject'];
                $facultyId = $row['faculty_id'];
                for ($ti = 0; $ti < 2; $ti++) {
                    $topicLabel = 'Unit '.($ti + 1).': Guided study — '.$subject->name;
                    $topic = Topic::firstOrCreate(
                        ['subject_id' => $subject->id, 'name' => $topicLabel],
                        [
                            'sort_order' => $ti,
                            'is_completed' => false,
                            'teaching_method_keys' => fake()->randomElements([
                                'demonstration', 'presentation', 'seminar', 'lab_demo', 'skill_simulation',
                            ], 3),
                        ]
                    );

                    $type = match ($ti % 3) {
                        0 => Assignment::TYPE_FILE_UPLOAD,
                        1 => Assignment::TYPE_CHECKLIST,
                        default => Assignment::TYPE_MIXED,
                    };

                    $checklist = [];
                    if ($type !== Assignment::TYPE_FILE_UPLOAD) {
                        $checklist = [
                            ['label' => 'Read learning outcomes', 'points' => 1],
                            ['label' => 'Complete practical steps safely', 'points' => 2],
                            ['label' => 'Document observations', 'points' => 1],
                        ];
                    }

                    $assignment = Assignment::firstOrCreate(
                        [
                            'topic_id' => $topic->id,
                            'title' => 'Homework: '.$topic->name,
                        ],
                        [
                            'description' => 'Demo homework for load testing. Submit before the due date.',
                            'due_date' => Carbon::now()->addDays(21),
                            'assignment_type' => $type,
                            'checklist_items' => $checklist,
                            'assessment_type_keys' => ['formative', 'quiz'],
                            'is_formative' => true,
                            'is_summative' => false,
                            'eval_includes_mcq' => $ti === 0,
                            'eval_includes_practical' => true,
                            'eval_includes_viva' => false,
                            'eval_includes_checklist' => $type !== Assignment::TYPE_FILE_UPLOAD,
                        ]
                    );

                    if ($idx < 4 && $ti === 0) {
                        $assignmentsForSubmissions[] = $assignment;
                    }
                }
            }

            $firstBatchSubjects = array_values(array_filter($allSubjects, fn ($r) => $r['batch']->id === $batches[0]->id));
            if (count($firstBatchSubjects) >= 2) {
                $s0 = $firstBatchSubjects[0]['subject'];
                $s1 = $firstBatchSubjects[1]['subject'];
                $topic0 = $s0->topics()->orderBy('id')->first();
                $topic1 = $s1->topics()->orderBy('id')->first();
                if ($topic0 && $topic1) {
                    $quizAssign = Assignment::updateOrCreate(
                        ['topic_id' => $topic0->id, 'title' => 'Quick quiz: '.$s0->name.' checkpoint'],
                        [
                            'description' => 'Short MCQ checkpoint.',
                            'due_date' => Carbon::now()->addDays(10),
                            'assignment_type' => Assignment::TYPE_QUIZ,
                            'assessment_type_keys' => ['quiz', 'formative'],
                            'is_formative' => true,
                            'is_summative' => false,
                            'eval_includes_mcq' => true,
                            'eval_includes_practical' => false,
                            'eval_includes_viva' => false,
                            'eval_includes_checklist' => false,
                        ]
                    );

                    $exam = AcademicExam::firstOrCreate(
                        [
                            'institution_id' => $institution->id,
                            'title' => 'Quick MCQ: '.$s0->name.' ('.$code.')',
                        ],
                        [
                            'created_by' => $facultyIds[0],
                            'audience_type' => AcademicExam::AUDIENCE_SUBJECT_COHORT,
                            'subject_id' => $s0->id,
                            'batch_id' => null,
                            'assignment_id' => $quizAssign->id,
                            'instructions' => 'Select the best answer.',
                            'duration_minutes' => 15,
                            'max_attempts' => 3,
                            'shuffle_questions' => false,
                            'shuffle_options' => false,
                            'is_published' => true,
                            'published_at' => now(),
                            'opens_at' => now()->subDay(),
                            'closes_at' => now()->addMonths(6),
                            'allows_cross_institution' => false,
                        ]
                    );

                    if ($exam->questions()->count() === 0) {
                        $q1 = $exam->questions()->create([
                            'body' => 'Which action is essential before a sterile procedure?',
                            'question_type' => 'mcq_single',
                            'sort_order' => 1,
                            'points' => 1,
                        ]);
                        $q1->options()->createMany([
                            ['label' => 'A', 'body' => 'Hand hygiene', 'is_correct' => true, 'sort_order' => 0],
                            ['label' => 'B', 'body' => 'Dim lights', 'is_correct' => false, 'sort_order' => 1],
                            ['label' => 'C', 'body' => 'Open window', 'is_correct' => false, 'sort_order' => 2],
                            ['label' => 'D', 'body' => 'Remove patient ID band', 'is_correct' => false, 'sort_order' => 3],
                        ]);
                        $q2 = $exam->questions()->create([
                            'body' => 'Patient education should be:',
                            'question_type' => 'mcq_single',
                            'sort_order' => 2,
                            'points' => 1,
                        ]);
                        $q2->options()->createMany([
                            ['label' => 'A', 'body' => 'Culturally appropriate and clear', 'is_correct' => true, 'sort_order' => 0],
                            ['label' => 'B', 'body' => 'Limited to written notes only', 'is_correct' => false, 'sort_order' => 1],
                            ['label' => 'C', 'body' => 'Avoided until discharge', 'is_correct' => false, 'sort_order' => 2],
                            ['label' => 'D', 'body' => 'Given only to family', 'is_correct' => false, 'sort_order' => 3],
                        ]);
                    }

                    $secondQuiz = Assignment::updateOrCreate(
                        ['topic_id' => $topic1->id, 'title' => 'Quick quiz: '.$s1->name.' checkpoint'],
                        [
                            'description' => 'Second quick quiz.',
                            'due_date' => Carbon::now()->addDays(12),
                            'assignment_type' => Assignment::TYPE_QUIZ,
                            'assessment_type_keys' => ['quiz'],
                            'is_formative' => true,
                            'is_summative' => false,
                            'eval_includes_mcq' => true,
                            'eval_includes_practical' => false,
                            'eval_includes_viva' => false,
                            'eval_includes_checklist' => false,
                        ]
                    );

                    $examB = AcademicExam::firstOrCreate(
                        [
                            'institution_id' => $institution->id,
                            'title' => 'Quick MCQ: '.$s1->name.' ('.$code.')',
                        ],
                        [
                            'created_by' => $facultyIds[1],
                            'audience_type' => AcademicExam::AUDIENCE_SUBJECT_COHORT,
                            'subject_id' => $s1->id,
                            'batch_id' => null,
                            'assignment_id' => $secondQuiz->id,
                            'instructions' => 'Select the best answer.',
                            'duration_minutes' => 12,
                            'max_attempts' => 2,
                            'shuffle_questions' => false,
                            'shuffle_options' => false,
                            'is_published' => true,
                            'published_at' => now(),
                            'opens_at' => now()->subDay(),
                            'closes_at' => now()->addMonths(6),
                            'allows_cross_institution' => false,
                        ]
                    );

                    if ($examB->questions()->count() === 0) {
                        $q = $examB->questions()->create([
                            'body' => 'The nurse’s first priority in community assessment is:',
                            'question_type' => 'mcq_single',
                            'sort_order' => 1,
                            'points' => 1,
                        ]);
                        $q->options()->createMany([
                            ['label' => 'A', 'body' => 'Community needs and strengths', 'is_correct' => true, 'sort_order' => 0],
                            ['label' => 'B', 'body' => 'Hospital revenue', 'is_correct' => false, 'sort_order' => 1],
                            ['label' => 'C', 'body' => 'Stock market', 'is_correct' => false, 'sort_order' => 2],
                            ['label' => 'D', 'body' => 'Equipment sales', 'is_correct' => false, 'sort_order' => 3],
                        ]);
                    }

                    foreach ($batch1Students as $sid) {
                        if (($sid + $c) % 3 === 0) {
                            continue;
                        }
                        AcademicExamAttempt::firstOrCreate(
                            ['exam_id' => $exam->id, 'user_id' => $sid],
                            [
                                'status' => AcademicExamAttempt::STATUS_SUBMITTED,
                                'started_at' => now()->subDays(2),
                                'submitted_at' => now()->subDay(),
                                'score' => fake()->randomFloat(2, 1, 2),
                            ]
                        );
                    }

                    foreach ($batch1Students as $sid) {
                        if (($sid + $c + 1) % 4 === 0) {
                            continue;
                        }
                        AcademicExamAttempt::firstOrCreate(
                            ['exam_id' => $examB->id, 'user_id' => $sid],
                            [
                                'status' => AcademicExamAttempt::STATUS_SUBMITTED,
                                'started_at' => now()->subDays(3),
                                'submitted_at' => now()->subDays(2),
                                'score' => fake()->randomFloat(2, 0.5, 1),
                            ]
                        );
                    }
                }
            }

            $demoFileRel = 'demo-bulk/submissions/placeholder-'.$code.'.pdf';
            if ($pdf !== '' && ! Storage::disk('public')->exists($demoFileRel)) {
                Storage::disk('public')->put($demoFileRel, $pdf);
            }

            foreach ($assignmentsForSubmissions as $asg) {
                foreach ($batch1Students as $idx => $sid) {
                    if ($idx % 4 === 0) {
                        continue;
                    }
                    if ($asg->assignment_type === Assignment::TYPE_FILE_UPLOAD) {
                        Submission::firstOrCreate(
                            ['assignment_id' => $asg->id, 'user_id' => $sid],
                            [
                                'file_path' => $demoFileRel,
                                'original_name' => 'homework-'.$asg->id.'-'.$sid.'.pdf',
                                'submitted_at' => now()->subHours(rand(1, 120)),
                                'notes' => 'Seeded submission for load testing.',
                            ]
                        );
                    } elseif ($asg->studentMustCompleteChecklist()) {
                        $items = $asg->normalizedChecklistItems();
                        $possible = array_sum(array_column($items, 'points'));
                        $earned = max(0, $possible - 1);
                        $answers = [];
                        foreach (array_keys($items) as $i) {
                            $answers[(string) $i] = true;
                        }
                        Submission::firstOrCreate(
                            ['assignment_id' => $asg->id, 'user_id' => $sid],
                            [
                                'file_path' => null,
                                'original_name' => null,
                                'submitted_at' => now()->subHours(rand(1, 96)),
                                'notes' => null,
                                'checklist_answers' => $answers,
                                'checklist_points_earned' => $earned,
                                'checklist_points_possible' => $possible,
                            ]
                        );
                    }
                }
            }

            foreach ($assignmentsForSubmissions as $asg) {
                foreach ($batch2Students as $idx => $sid) {
                    if ($idx % 5 === 0) {
                        continue;
                    }
                    if ($asg->assignment_type === Assignment::TYPE_FILE_UPLOAD) {
                        Submission::firstOrCreate(
                            ['assignment_id' => $asg->id, 'user_id' => $sid],
                            [
                                'file_path' => $demoFileRel,
                                'original_name' => 'homework-'.$asg->id.'-'.$sid.'.pdf',
                                'submitted_at' => now()->subHours(rand(1, 120)),
                                'notes' => 'Seeded submission for load testing.',
                            ]
                        );
                    } elseif ($asg->studentMustCompleteChecklist()) {
                        $items = $asg->normalizedChecklistItems();
                        $possible = array_sum(array_column($items, 'points'));
                        $earned = max(0, $possible - 1);
                        $answers = [];
                        foreach (array_keys($items) as $i) {
                            $answers[(string) $i] = true;
                        }
                        Submission::firstOrCreate(
                            ['assignment_id' => $asg->id, 'user_id' => $sid],
                            [
                                'file_path' => null,
                                'original_name' => null,
                                'submitted_at' => now()->subHours(rand(1, 96)),
                                'notes' => null,
                                'checklist_answers' => $answers,
                                'checklist_points_earned' => $earned,
                                'checklist_points_possible' => $possible,
                            ]
                        );
                    }
                }
            }

            if (! empty($assignmentsForSubmissions)) {
                $primary = $assignmentsForSubmissions[0];
                foreach (array_merge($batch1Students, $batch2Students) as $sid) {
                    if ($primary->assignment_type === Assignment::TYPE_FILE_UPLOAD) {
                        Submission::firstOrCreate(
                            ['assignment_id' => $primary->id, 'user_id' => $sid],
                            [
                                'file_path' => $demoFileRel,
                                'original_name' => 'homework-'.$primary->id.'-'.$sid.'-all.pdf',
                                'submitted_at' => now()->subHours(rand(1, 96)),
                                'notes' => 'Seeded submission for load testing.',
                            ]
                        );
                    } elseif ($primary->studentMustCompleteChecklist()) {
                        $items = $primary->normalizedChecklistItems();
                        $possible = array_sum(array_column($items, 'points'));
                        $earned = max(0, $possible - 1);
                        $answers = [];
                        foreach (array_keys($items) as $i) {
                            $answers[(string) $i] = true;
                        }
                        Submission::firstOrCreate(
                            ['assignment_id' => $primary->id, 'user_id' => $sid],
                            [
                                'file_path' => null,
                                'original_name' => null,
                                'submitted_at' => now()->subHours(rand(1, 72)),
                                'notes' => null,
                                'checklist_answers' => $answers,
                                'checklist_points_earned' => $earned,
                                'checklist_points_possible' => $possible,
                            ]
                        );
                    }
                }
            }

            $this->syncAcademicInstitutionFromBatches((int) $institution->id);

            $this->enrichProfile($admin, $png, $pdf, full: true);
            foreach ($facultyIds as $fid) {
                $this->enrichProfile(User::find($fid), $png, $pdf, full: true);
            }
            foreach ($studentIds as $i => $sid) {
                $full = ($i + $c) % 8 !== 0;
                $this->enrichProfile(User::find($sid), $png, $pdf, full: $full);
            }

            $this->command?->info("  ✓ {$code} — {$institution->name}");
        }

        $this->command?->info('');
        $this->command?->info('Bulk academics seeded. Login pattern (password: '.self::DEMO_PASSWORD.'):');
        $this->command?->info('  Admin:   {CODE}-admin@academic-bulk.demo   e.g. bulk-agn-01-admin@…');
        $this->command?->info('  Faculty: {CODE}-f01@academic-bulk.demo … f06');
        $this->command?->info('  Student: {CODE}-s001@academic-bulk.demo … s018');
        $this->command?->info('CODE is lowercase institution code (see institutions.code BULK-*).');
    }

    /** Sync users.academic_institution_id from batch membership for this college. */
    private function syncAcademicInstitutionFromBatches(int $institutionId): void
    {
        $batchIds = Batch::query()->where('institution_id', $institutionId)->pluck('id');
        if ($batchIds->isEmpty()) {
            return;
        }

        $userIds = DB::table('academic_batch_users')
            ->whereIn('batch_id', $batchIds)
            ->whereIn('type', ['student', 'faculty'])
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();

        if ($userIds !== []) {
            User::query()->whereIn('id', $userIds)->update([
                'academic_institution_id' => $institutionId,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function makeUser(array $attrs): User
    {
        $attrs['phone'] = $attrs['phone'] ?? $this->nextIndianMobile();
        $attrs['is_active'] = true;
        $attrs['email_verified_at'] = now();
        $f = $this->indianFaker();
        $attrs['address'] = $attrs['address'] ?? $f->streetAddress().', '.$f->city().', '.$f->state();
        $attrs['pincode'] = $attrs['pincode'] ?? (string) $f->numberBetween(110001, 829999);
        $attrs['date_of_birth'] = $attrs['date_of_birth'] ?? $f->dateTimeBetween('-28 years', '-17 years')->format('Y-m-d');

        $email = $attrs['email'];
        unset($attrs['email']);

        return User::updateOrCreate(['email' => $email], $attrs);
    }

    private function enrichProfile(?User $user, string $png, string $pdf, bool $full): void
    {
        if (! $user) {
            return;
        }

        $avatarPath = null;
        if ($full && $png !== '') {
            $avatarPath = 'demo-bulk/avatars/'.$user->id.'.png';
            Storage::disk('public')->put($avatarPath, $png);
        }

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'bio' => $full ? $this->indianFaker()->sentence(12) : $this->indianFaker()->sentence(6),
                'experience_years' => $user->role === 'student' ? $this->indianFaker()->numberBetween(0, 2) : $this->indianFaker()->numberBetween(5, 22),
                'specialization' => $user->role === 'student'
                    ? 'B.Sc Nursing (student)'
                    : fake()->randomElement(['Medical-Surgical', 'Community Health', 'Anatomy', 'Pediatrics', 'Mental Health']),
                'availability_status' => 'available',
                'avatar_path' => $avatarPath,
                'is_profile_complete' => $full,
            ]
        );

        if (! $full || $pdf === '' || $png === '') {
            return;
        }

        if (! Document::where('user_id', $user->id)->where('document_type', 'id_proof')->exists()) {
            $idPath = 'documents/'.$user->id.'/seed-id-'.Str::uuid().'.png';
            Storage::disk('public')->put($idPath, $png);
            Document::create([
                'user_id' => $user->id,
                'document_type' => 'id_proof',
                'document_name' => 'Government ID (demo)',
                'original_name' => 'id-demo.png',
                'file_path' => $idPath,
                'file_size' => strlen($png),
                'mime_type' => 'image/png',
                'status' => fake()->randomElement(['uploaded', 'verified']),
            ]);
        }

        if (! Document::where('user_id', $user->id)->where('document_type', 'certificate')->exists()) {
            $certPath = 'documents/'.$user->id.'/seed-cert-'.Str::uuid().'.pdf';
            Storage::disk('public')->put($certPath, $pdf);
            Document::create([
                'user_id' => $user->id,
                'document_type' => 'certificate',
                'document_name' => $user->role === 'student' ? '10+2 Mark sheet (demo)' : 'Academic certificate (demo)',
                'original_name' => 'certificate-demo.pdf',
                'file_path' => $certPath,
                'file_size' => strlen($pdf),
                'mime_type' => 'application/pdf',
                'status' => fake()->randomElement(['uploaded', 'verified']),
            ]);
        }
    }
}
