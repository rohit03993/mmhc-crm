<?php

use Illuminate\Database\Migrations\Migration;

/**
 * OSCE tables were removed from the product scope (Phases A–B only).
 * Retain this migration file so existing migration history stays valid; new installs skip creating these tables.
 * Migration `2026_05_07_000001_drop_academic_osce_tables` drops tables on databases that already had OSCE.
 */
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
