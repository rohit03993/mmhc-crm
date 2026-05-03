<?php

namespace Tests\Feature;

use App\Models\Core\User;
use App\Modules\Auth\Controllers\DashboardController;
use App\Modules\Payments\Services\StaffPayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class DashboardRedirectTest extends TestCase
{
    public function test_admin_user_is_redirected_to_community(): void
    {
        $user = new User([
            'name' => 'Admin User',
            'email' => 'admin@test.local',
            'role' => 'admin',
        ]);

        Auth::shouldReceive('user')->once()->andReturn($user);
        $controller = new DashboardController(\Mockery::mock(StaffPayoutService::class));
        $response = $controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('community.index'), $response->getTargetUrl());
    }

    public function test_staff_user_is_redirected_to_community(): void
    {
        $user = new User([
            'name' => 'Nurse User',
            'email' => 'nurse@test.local',
            'role' => 'nurse',
        ]);

        Auth::shouldReceive('user')->once()->andReturn($user);
        $controller = new DashboardController(\Mockery::mock(StaffPayoutService::class));
        $response = $controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('community.index'), $response->getTargetUrl());
    }

    public function test_patient_user_is_redirected_to_community(): void
    {
        $user = new User([
            'name' => 'Patient User',
            'email' => 'patient@test.local',
            'role' => 'patient',
        ]);

        Auth::shouldReceive('user')->once()->andReturn($user);
        $controller = new DashboardController(\Mockery::mock(StaffPayoutService::class));
        $response = $controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('community.index'), $response->getTargetUrl());
    }

    public function test_academic_user_is_redirected_to_academics_dashboard(): void
    {
        $user = new User([
            'name' => 'Faculty User',
            'email' => 'faculty@test.local',
            'role' => 'faculty',
        ]);

        Auth::shouldReceive('user')->once()->andReturn($user);
        $controller = new DashboardController(\Mockery::mock(StaffPayoutService::class));
        $response = $controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('academics.dashboard'), $response->getTargetUrl());
    }
}
