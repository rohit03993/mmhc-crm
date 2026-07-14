<?php

namespace Tests\Unit;

use App\Models\Core\User;
use App\Modules\Services\Models\ServiceRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ServiceRequestCancelRulesTest extends TestCase
{
    public function test_patient_can_cancel_pending_own_request(): void
    {
        $patient = $this->makePatient(10);
        $request = $this->makeRequest(10, 'pending');

        $this->assertTrue($request->canBeCancelledByPatient($patient));
    }

    public function test_patient_can_cancel_pending_approval_own_request(): void
    {
        $patient = $this->makePatient(11);
        $request = $this->makeRequest(11, 'pending_approval');

        $this->assertTrue($request->canBeCancelledByPatient($patient));
    }

    public function test_patient_cannot_cancel_assigned_request(): void
    {
        $patient = $this->makePatient(12);
        $request = $this->makeRequest(12, 'assigned');

        $this->assertFalse($request->canBeCancelledByPatient($patient));
    }

    public function test_patient_cannot_cancel_in_progress_or_completed(): void
    {
        $patient = $this->makePatient(13);

        $this->assertFalse($this->makeRequest(13, 'in_progress')->canBeCancelledByPatient($patient));
        $this->assertFalse($this->makeRequest(13, 'completed')->canBeCancelledByPatient($patient));
        $this->assertFalse($this->makeRequest(13, 'cancelled')->canBeCancelledByPatient($patient));
    }

    public function test_patient_cannot_cancel_another_patients_request(): void
    {
        $patient = $this->makePatient(20);
        $request = $this->makeRequest(21, 'pending');

        $this->assertFalse($request->canBeCancelledByPatient($patient));
    }

    public function test_staff_or_admin_cannot_use_patient_cancel_helper(): void
    {
        $request = $this->makeRequest(30, 'pending');

        $nurse = new User(['role' => 'nurse']);
        $nurse->id = 30;

        $admin = new User(['role' => 'admin']);
        $admin->id = 30;

        $this->assertFalse($request->canBeCancelledByPatient($nurse));
        $this->assertFalse($request->canBeCancelledByPatient($admin));
    }

    public function test_assigned_staff_can_cancel_before_visit_starts(): void
    {
        $nurse = $this->makeStaff(40, 'nurse');

        $this->assertTrue($this->makeAssignedRequest(40, 'pending_approval')->canBeCancelledByStaff($nurse));
        $this->assertTrue($this->makeAssignedRequest(40, 'assigned')->canBeCancelledByStaff($nurse));
    }

    public function test_staff_cannot_cancel_after_visit_starts(): void
    {
        $nurse = $this->makeStaff(41, 'nurse');

        $this->assertFalse($this->makeAssignedRequest(41, 'in_progress')->canBeCancelledByStaff($nurse));
        $this->assertFalse($this->makeAssignedRequest(41, 'completed')->canBeCancelledByStaff($nurse));
    }

    public function test_other_staff_or_admin_cannot_cancel(): void
    {
        $assigned = $this->makeAssignedRequest(50, 'assigned');
        $other = $this->makeStaff(99, 'caregiver');
        $admin = new User(['role' => 'admin']);
        $admin->id = 1;

        $this->assertFalse($assigned->canBeCancelledByStaff($other));
        $this->assertFalse($assigned->canBeCancelledByStaff($admin));
    }

    public function test_paid_cancelled_flags_refund_due(): void
    {
        $request = new ServiceRequest([
            'total_amount' => 500,
            'payment_status' => 'paid',
            'refund_due_at' => now(),
            'refunded_at' => null,
        ]);

        $this->assertTrue($request->isRefundDue());
        $this->assertTrue($request->shouldQueueManualRefundOnCancel());
    }

    #[DataProvider('cancellableStatusesProvider')]
    public function test_transition_matrix_allows_cancel_from_cancellable(string $status): void
    {
        $request = $this->makeRequest(1, $status);
        $this->assertTrue($request->canTransitionTo('cancelled'));
    }

    public static function cancellableStatusesProvider(): array
    {
        return [
            ['pending'],
            ['pending_approval'],
            ['assigned'],
        ];
    }

    private function makePatient(int $id): User
    {
        $user = new User([
            'name' => 'Patient '.$id,
            'email' => "patient{$id}@test.local",
            'role' => 'patient',
        ]);
        $user->id = $id;

        return $user;
    }

    private function makeStaff(int $id, string $role): User
    {
        $user = new User([
            'name' => 'Staff '.$id,
            'email' => "staff{$id}@test.local",
            'role' => $role,
        ]);
        $user->id = $id;

        return $user;
    }

    private function makeRequest(int $patientId, string $status): ServiceRequest
    {
        return new ServiceRequest([
            'patient_id' => $patientId,
            'status' => $status,
        ]);
    }

    private function makeAssignedRequest(int $staffId, string $status): ServiceRequest
    {
        return new ServiceRequest([
            'patient_id' => 1,
            'assigned_staff_id' => $staffId,
            'status' => $status,
        ]);
    }
}
