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
        Schema::create('document_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('document_type')->unique();  // e.g., 'pull_out_receipt'
            $table->string('form_code');                // e.g., 'ADM-WHS-003'
            $table->string('ref_prefix'); // e.g., 'PO'
            $table->string('display_name');              
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_definitions');
    }
};
