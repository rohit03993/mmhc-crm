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

    private function makeRequest(int $patientId, string $status): ServiceRequest
    {
        $request = new ServiceRequest([
            'patient_id' => $patientId,
            'status' => $status,
        ]);

        return $request;
    }
}
