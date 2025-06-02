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
            $table->unsignedBigInteger('user_id')->nullable(); // To link to the user who created it
            $table->string('document_name');
            $table->string('document_type'); // e.g., 'purchase_request', 'pull_out_receipt'
            $table->string('recipient')->nullable();
            $table->string('status')->default('draft'); // e.g., 'draft', 'sent', 'signed', 'archived'
            $table->json('data')->nullable(); // To store the form's field values as JSON
            $table->string('file_path')->nullable(); // If you store generated PDF/Excel files
            $table->timestamps(); // Adds created_at and updated_at columns

            // Optional: Add a foreign key constraint if you have a users table
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null'); // or 'cascade'
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