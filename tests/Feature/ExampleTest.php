<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class ExampleTest extends TestCase
{
    /**
     * Login route may touch DB (e.g. site settings). Requires SQLite PDO in CI/local.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get(route('auth.login'));

        $response->assertStatus(200);
    }
}
