<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_growth_dta_slabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_set_id')->constrained('incentive_rule_sets')->cascadeOnDelete();
            $table->unsignedInteger('min_inclusive');
            $table->unsignedInteger('max_exclusive')->nullable();
            $table->decimal('growth_percent', 8, 4);
            $table->decimal('dta_percent', 8, 4);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['rule_set_id', 'min_inclusive']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_growth_dta_slabs');
    }
};
