<?php

namespace Tests\Unit;

use App\Modules\Services\Models\ServiceRequest;
use Tests\TestCase;

class ServiceRequestVisitPaymentTest extends TestCase
{
    public function test_free_visit_is_settled(): void
    {
        $request = new ServiceRequest([
            'total_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $this->assertFalse($request->requiresVisitPayment());
        $this->assertTrue($request->isVisitPaymentSettled());
    }

    public function test_unpaid_visit_requires_payment(): void
    {
        $request = new ServiceRequest([
            'total_amount' => 1500,
            'payment_status' => 'pending',
        ]);

        $this->assertTrue($request->requiresVisitPayment());
        $this->assertFalse($request->isVisitPaymentSettled());
    }

    public function test_paid_visit_is_settled(): void
    {
        $request = new ServiceRequest([
            'total_amount' => 1500,
            'payment_status' => 'paid',
        ]);

        $this->assertTrue($request->requiresVisitPayment());
        $this->assertTrue($request->isVisitPaymentSettled());
    }
}
