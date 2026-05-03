<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

/**
 * Smoke tests for key guest-facing routes.
 * Guest pages may resolve site branding via DB; SQLite PDO must be enabled for these to run.
 */
#[RequiresPhpExtension('pdo_sqlite')]
class SmokeTest extends TestCase
{
    public function test_login_page_returns_ok(): void
    {
        $response = $this->get(route('auth.login'));

        $response->assertStatus(200);
    }

    public function test_register_page_returns_ok(): void
    {
        $response = $this->get(route('auth.register'));

        $response->assertStatus(200);
    }
}
