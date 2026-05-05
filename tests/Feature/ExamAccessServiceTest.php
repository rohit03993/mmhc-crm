<?php

namespace Tests\Feature;

use App\Models\Core\User;
use App\Modules\Academics\Models\AcademicExam;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Services\ExamAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExamAccessServiceTest extends TestCase
{
    public function test_subject_cohort_student_in_batch_can_take_when_faculty_published(): void
    {
        $service = new ExamAccessService;
        $ctx = $this->makeInstitutionWithSubjectAndUsers();

        $exam = AcademicExam::create([
            'institution_id' => $ctx['institution']->id,
            'created_by' => $ctx['faculty']->id,
            'audience_type' => AcademicExam::AUDIENCE_SUBJECT_COHORT,
            'subject_id' => $ctx['subject']->id,
            'batch_id' => null,
            'title' => 'Anatomy quiz',
            'is_published' => true,
            'published_at' => now(),
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ]);

        $this->assertTrue($service->canTake($ctx['student'], $exam));
        $this->assertFalse($service->canTake($ctx['other_student'], $exam));
        $this->assertTrue($service->canManage($ctx['faculty'], $exam));
        $this->assertFalse($service->canManage($ctx['other_faculty'], $exam));
    }

    public function test_institution_open_allows_all_students_in_college(): void
    {
        $service = new ExamAccessService;
        $ctx = $this->makeInstitutionWithSubjectAndUsers();

        $exam = AcademicExam::create([
            'institution_id' => $ctx['institution']->id,
            'created_by' => $ctx['institution_admin']->id,
            'audience_type' => AcademicExam::AUDIENCE_INSTITUTION_OPEN,
            'subject_id' => null,
            'batch_id' => null,
            'title' => 'Mock test',
            'is_published' => true,
            'published_at' => now(),
            'opens_at' => now()->subHour(),
            'closes_at' => null,
        ]);

        $this->assertTrue($service->canTake($ctx['student'], $exam));
        $this->assertFalse($service->canTake($ctx['other_student'], $exam));
        $this->assertTrue($service->canManage($ctx['institution_admin'], $exam));
    }

    public function test_unpublished_exam_not_takable(): void
    {
        $service = new ExamAccessService;
        $ctx = $this->makeInstitutionWithSubjectAndUsers();

        $exam = AcademicExam::create([
            'institution_id' => $ctx['institution']->id,
            'created_by' => $ctx['faculty']->id,
            'audience_type' => AcademicExam::AUDIENCE_SUBJECT_COHORT,
            'subject_id' => $ctx['subject']->id,
            'title' => 'Draft',
            'is_published' => false,
            'opens_at' => now()->subHour(),
        ]);

        $this->assertFalse($service->canTake($ctx['student'], $exam));
        $this->assertFalse($service->studentCanViewPublishedExam($ctx['student'], $exam));
    }

    public function test_student_can_browse_upcoming_published_exam_but_not_take_until_open(): void
    {
        $service = new ExamAccessService;
        $ctx = $this->makeInstitutionWithSubjectAndUsers();

        $exam = AcademicExam::create([
            'institution_id' => $ctx['institution']->id,
            'created_by' => $ctx['faculty']->id,
            'audience_type' => AcademicExam::AUDIENCE_SUBJECT_COHORT,
            'subject_id' => $ctx['subject']->id,
            'batch_id' => null,
            'title' => 'Future quiz',
            'is_published' => true,
            'published_at' => now(),
            'opens_at' => now()->addDay(),
            'closes_at' => now()->addWeek(),
        ]);

        $this->assertTrue($service->studentCanViewPublishedExam($ctx['student'], $exam));
        $this->assertFalse($service->canTake($ctx['student'], $exam));
    }

    /**
     * @return array{institution: Institution, batch: Batch, subject: Subject, faculty: User, other_faculty: User, student: User, other_student: User, institution_admin: User}
     */
    protected function makeInstitutionWithSubjectAndUsers(): array
    {
        $institution = Institution::create([
            'name' => 'Test College',
            'code' => 'TC-01',
            'is_active' => true,
        ]);

        $institution2 = Institution::create([
            'name' => 'Other College',
            'code' => 'OC-01',
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'institution_id' => $institution->id,
            'name' => 'B.Sc 2024',
            'is_active' => true,
        ]);

        $otherBatch = Batch::create([
            'institution_id' => $institution2->id,
            'name' => 'Other batch',
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'batch_id' => $batch->id,
            'name' => 'Anatomy',
            'code' => 'ANAT',
            'is_active' => true,
        ]);

        $defaultLocation = DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");

        $faculty = User::create([
            'name' => 'Faculty One',
            'email' => 'f1@exam-access.test',
            'phone' => '9810000001',
            'location' => $defaultLocation,
            'password' => Hash::make('password'),
            'role' => 'faculty',
            'unique_id' => 'F1-EX',
            'academic_institution_id' => $institution->id,
            'is_active' => true,
        ]);

        $other_faculty = User::create([
            'name' => 'Faculty Two',
            'email' => 'f2@exam-access.test',
            'phone' => '9810000002',
            'location' => $defaultLocation,
            'password' => Hash::make('password'),
            'role' => 'faculty',
            'unique_id' => 'F2-EX',
            'academic_institution_id' => $institution->id,
            'is_active' => true,
        ]);

        $student = User::create([
            'name' => 'Student In',
            'email' => 's1@exam-access.test',
            'phone' => '9810000003',
            'location' => $defaultLocation,
            'password' => Hash::make('password'),
            'role' => 'student',
            'unique_id' => 'S1-EX',
            'academic_institution_id' => $institution->id,
            'is_active' => true,
        ]);

        $other_student = User::create([
            'name' => 'Student Other',
            'email' => 's2@exam-access.test',
            'phone' => '9810000004',
            'location' => $defaultLocation,
            'password' => Hash::make('password'),
            'role' => 'student',
            'unique_id' => 'S2-EX',
            'academic_institution_id' => $institution2->id,
            'is_active' => true,
        ]);

        $institution_admin = User::create([
            'name' => 'Admin',
            'email' => 'ia@exam-access.test',
            'phone' => '9810000005',
            'location' => $defaultLocation,
            'password' => Hash::make('password'),
            'role' => 'institution_admin',
            'unique_id' => 'IA-EX',
            'academic_institution_id' => $institution->id,
            'is_active' => true,
        ]);

        DB::table('academic_batch_users')->insert([
            ['batch_id' => $batch->id, 'user_id' => $student->id, 'type' => 'student', 'created_at' => now(), 'updated_at' => now()],
            ['batch_id' => $batch->id, 'user_id' => $faculty->id, 'type' => 'faculty', 'created_at' => now(), 'updated_at' => now()],
            ['batch_id' => $batch->id, 'user_id' => $other_faculty->id, 'type' => 'faculty', 'created_at' => now(), 'updated_at' => now()],
            ['batch_id' => $otherBatch->id, 'user_id' => $other_student->id, 'type' => 'student', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $subject->faculty()->sync([$faculty->id]);

        return [
            'institution' => $institution,
            'batch' => $batch,
            'subject' => $subject,
            'faculty' => $faculty,
            'other_faculty' => $other_faculty,
            'student' => $student,
            'other_student' => $other_student,
            'institution_admin' => $institution_admin,
        ];
    }
}
