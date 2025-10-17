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
        Schema::create('healthcare_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basic Plan, Standard Plan, etc.
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Price in rupees
            $table->string('currency', 3)->default('INR');
            $table->integer('duration_days')->default(30); // Monthly, yearly, etc.
            $table->json('features'); // Array of features
            $table->string('icon_class')->nullable(); // FontAwesome icon class
            $table->string('color_theme')->default('blue'); // blue, green, purple, orange
            $table->boolean('is_popular')->default(false); // Most Popular badge
            $table->string('popular_label')->nullable(); // "Most Popular", "Family Choice", etc.
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('button_text')->default('Get Started');
            $table->string('button_link')->nullable(); // Custom link if needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('healthcare_plans');
    }
};
