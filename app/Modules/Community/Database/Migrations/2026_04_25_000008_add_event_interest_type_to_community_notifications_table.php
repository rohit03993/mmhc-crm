<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE community_notifications MODIFY type ENUM('reaction','comment','event_interest') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE community_notifications SET type = 'comment' WHERE type = 'event_interest'");
        DB::statement("ALTER TABLE community_notifications MODIFY type ENUM('reaction','comment') NOT NULL");
    }
};

