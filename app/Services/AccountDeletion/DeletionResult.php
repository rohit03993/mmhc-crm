<?php

namespace App\Services\AccountDeletion;

class DeletionResult
{
    public function __construct(
        public bool $success,
        public string $message = '',
        public array $stats = [],
    ) {}
}
