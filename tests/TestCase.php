<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension('pdo_mysql')]
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
}
