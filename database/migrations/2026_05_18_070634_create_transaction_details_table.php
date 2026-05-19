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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('amount')->nullable();
            $table->text('note')->nullable();
            $table->text('purpose')->nullable();
            // 'SUBMITTED', 'APPROVED', 'DONE', 'CANCELLED', 'REJECTED'
            $table->text('status')->nullable();
            $table->timestampsTz();
            $table->timestampTz('deleted_at')->nullable();

            $table->foreignUuid('transaction_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('transactions')
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
        Schema::dropIfExists('transaction_details');
    }
};
