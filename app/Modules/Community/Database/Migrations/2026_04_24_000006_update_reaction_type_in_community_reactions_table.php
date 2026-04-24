<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE community_reactions MODIFY reaction_type ENUM('like','care','support','celebrate') NOT NULL DEFAULT 'like'");
    }

    public function down(): void
    {
        DB::statement("UPDATE community_reactions SET reaction_type = 'like' WHERE reaction_type <> 'like'");
        DB::statement("ALTER TABLE community_reactions MODIFY reaction_type ENUM('like') NOT NULL DEFAULT 'like'");
    }
};

