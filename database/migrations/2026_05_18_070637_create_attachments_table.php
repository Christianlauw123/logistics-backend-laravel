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
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('amount')->nullable();
            $table->text('file_url')->nullable();
            $table->text('extracted_do_number')->nullable();
            $table->date('extracted_do_date')->nullable();
            // 'PENDING', 'COMPLETED', 'ERROR'
            $table->text('upload_status')->default('PENDING')->nullable();
            $table->text('upload_status_error')->nullable();
            // 'PENDING', 'VERIFIED', 'REJECTED'
            $table->text('status')->default('PENDING');
            $table->timestampTz('uploaded_at')->nullable();
            $table->timestampsTz();
            $table->timestampTz('deleted_at')->nullable();

            $table->foreignUuid('transaction_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('transactions')
                  ->nullOnDelete();
            $table->foreignUuid('transaction_detail_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('transaction_details')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
