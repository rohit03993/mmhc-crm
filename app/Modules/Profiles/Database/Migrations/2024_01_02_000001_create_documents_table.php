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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', ['certificate', 'id_proof', 'medical_license', 'insurance', 'other']);
            $table->string('document_name');
            $table->string('original_name');
            $table->string('file_path');
            $table->integer('file_size'); // in bytes
            $table->string('mime_type');
            $table->enum('status', ['uploaded', 'verified', 'rejected'])->default('uploaded');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('document_type');
            $table->index('status');
            $table->index(['user_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
