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
        Schema::table('plans', function (Blueprint $table) {
            $table->string('monthly_price')->nullable()->after('price');
            $table->string('members_included')->nullable()->after('description'); // e.g., "1 adult/child", "2 members", "2 adults + 1 child", "4 members"
            $table->json('payment_options')->nullable()->after('monthly_price'); // Store payment frequency options with pricing
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['monthly_price', 'members_included', 'payment_options']);
        });
    }
};

