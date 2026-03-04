<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Smoke tests for key guest-facing routes (no DB required).
 * Run with: php artisan test tests/Feature/SmokeTest.php
 *
 * Tests that hit the DB (e.g. login POST) are skipped with default config
 * because PHPUnit uses SQLite in-memory and no migrations run. See docs/TESTING.md.
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
