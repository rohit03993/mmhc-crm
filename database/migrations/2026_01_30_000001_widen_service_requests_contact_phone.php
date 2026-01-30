<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * contact_phone was VARCHAR(10); values like +918897894654 (13 chars) truncate.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE service_requests MODIFY contact_phone VARCHAR(20) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE service_requests MODIFY contact_phone VARCHAR(10) NOT NULL');
    }
};
