<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('community_notifications')) {
            // Recover from partial migration state: table exists but migration was not marked as ran.
            Schema::table('community_notifications', function (Blueprint $table) {
                if (!$this->indexExists('community_notifications', 'cn_rec_read_created_idx')) {
                    $table->index(['recipient_user_id', 'read_at', 'created_at'], 'cn_rec_read_created_idx');
                }
                if (!$this->indexExists('community_notifications', 'cn_post_type_idx')) {
                    $table->index(['post_id', 'type'], 'cn_post_type_idx');
                }
            });
            return;
        }

        Schema::create('community_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('post_id')->constrained('community_posts')->cascadeOnDelete();
            $table->enum('type', ['reaction', 'comment']);
            $table->json('meta')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_user_id', 'read_at', 'created_at'], 'cn_rec_read_created_idx');
            $table->index(['post_id', 'type'], 'cn_post_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_notifications');
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $result = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$tableName, $indexName]
        );

        return !empty($result);
    }
};

