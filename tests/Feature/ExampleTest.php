<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Login route may touch DB (e.g. site settings). MySQL (see phpunit.xml).
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get(route('auth.login'));

        $response->assertStatus(200);
    }
}
