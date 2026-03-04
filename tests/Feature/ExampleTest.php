<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Home route (/) requires DB (page_contents, etc.). With default PHPUnit config
     * (SQLite in-memory, no migrations) that fails. Test a public route that does not use DB.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get(route('auth.login'));

        $response->assertStatus(200);
    }
}
