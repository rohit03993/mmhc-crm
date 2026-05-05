<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('academic_osce_stations');
        Schema::dropIfExists('academic_osce_sessions');
    }

    public function down(): void
    {
        //
    }
};
