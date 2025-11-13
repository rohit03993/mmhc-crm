<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('caregiver_rewards', function (Blueprint $table) {
            $table->unsignedInteger('patient_age')->nullable()->after('patient_phone');
            $table->text('patient_address')->nullable()->after('patient_age');
            $table->string('patient_pincode', 6)->nullable()->after('patient_address');
            
            $table->index('patient_pincode');
        });
    }

    public function down(): void
    {
        Schema::table('caregiver_rewards', function (Blueprint $table) {
            $table->dropIndex(['patient_pincode']);
            $table->dropColumn(['patient_age', 'patient_address', 'patient_pincode']);
        });
    }
};

