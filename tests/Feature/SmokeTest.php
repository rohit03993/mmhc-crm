<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Smoke tests for key guest-facing routes.
 * Guest pages may touch the database (e.g. settings); MySQL is used (see phpunit.xml).
 */
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
