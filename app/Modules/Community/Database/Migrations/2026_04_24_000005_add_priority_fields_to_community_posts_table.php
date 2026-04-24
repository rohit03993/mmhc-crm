<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->after('post_type');
            $table->boolean('is_announcement')->default(false)->after('is_pinned');
            $table->timestamp('pinned_at')->nullable()->after('is_announcement');

            $table->index(['is_pinned', 'pinned_at']);
            $table->index(['is_announcement', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropIndex(['is_pinned', 'pinned_at']);
            $table->dropIndex(['is_announcement', 'created_at']);
            $table->dropColumn(['is_pinned', 'is_announcement', 'pinned_at']);
        });
    }
};

