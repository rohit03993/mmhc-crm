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
            $table->string('icon_class')->nullable()->after('sort_order'); // FontAwesome icon class
            $table->string('color_theme')->default('blue')->after('icon_class'); // blue, green, purple, orange
            $table->string('popular_label')->nullable()->after('is_popular'); // "Most Popular", "Family Choice", etc.
            $table->string('button_text')->default('Get Started')->after('popular_label');
            $table->string('button_link')->nullable()->after('button_text'); // Custom link if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'icon_class',
                'color_theme',
                'popular_label',
                'button_text',
                'button_link'
            ]);
        });
    }
};

