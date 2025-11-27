<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pincode', 6)->nullable()->after('address')->index();
            $table->decimal('latitude', 10, 8)->nullable()->after('pincode');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            
            // Index for faster distance queries
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropIndex(['pincode']);
            $table->dropColumn(['pincode', 'latitude', 'longitude']);
        });
    }
};

