<?php

namespace App\Services\AccountDeletion;

class BulkDeletionResult
{
    /**
     * @param  list<array{user_id: int, name: string, success: bool, message: string}>  $rows
     */
    public function __construct(
        public int $deleted,
        public int $skipped,
        public int $failed,
        public array $rows = [],
    ) {}
}
