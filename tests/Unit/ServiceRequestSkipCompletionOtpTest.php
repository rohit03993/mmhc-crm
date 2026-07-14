<?php

namespace Tests\Unit;

use App\Models\Core\User;
use App\Modules\Services\Models\ServiceRequest;
use Tests\TestCase;

class ServiceRequestSkipCompletionOtpTest extends TestCase
{
    public function test_skip_otp_when_staff_verified_phone_matches_patient_contact(): void
    {
        $staff = new User([
            'role' => 'nurse',
            'phone' => '9876543210',
            'phone_verified_at' => now(),
        ]);

        $request = new ServiceRequest([
            'contact_phone' => '9876543210',
            'status' => 'in_progress',
        ]);
        // Avoid loading patient relation
        $request->setRelation('patient', null);

        $this->assertTrue($request->staffMayCompleteWithoutPatientOtp($staff));
    }

    public function test_no_skip_when_phones_differ(): void
    {
        $staff = new User([
            'role' => 'nurse',
            'phone' => '9876543210',
            'phone_verified_at' => now(),
        ]);

        $request = new ServiceRequest([
            'contact_phone' => '9123456789',
            'status' => 'in_progress',
        ]);
        $request->setRelation('patient', null);

        $this->assertFalse($request->staffMayCompleteWithoutPatientOtp($staff));
    }

    public function test_no_skip_when_staff_phone_not_verified(): void
    {
        $staff = new User([
            'role' => 'nurse',
            'phone' => '9876543210',
            'phone_verified_at' => null,
        ]);

        $request = new ServiceRequest([
            'contact_phone' => '9876543210',
            'status' => 'in_progress',
        ]);
        $request->setRelation('patient', null);

        $this->assertFalse($request->staffMayCompleteWithoutPatientOtp($staff));
    }
}
