<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * skills was VARCHAR(255); admin form/validation allow up to 500 chars.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE featured_team MODIFY skills VARCHAR(500) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE featured_team MODIFY skills VARCHAR(255) NULL');
    }
};
