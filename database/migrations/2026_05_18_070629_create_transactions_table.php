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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('trip_price_amount')->nullable();

            $table->text('file_folder_id')->nullable();
            $table->text('file_sub_folder_id')->nullable();
            $table->text('file_provider')->nullable();

            $table->timestampsTz();
            $table->timestampTz('deleted_at')->nullable();

            $table->string('vehicle_plate')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->decimal('vehicle_capacity')->nullable();

            $table->decimal('transaction_capacity')->nullable();
            $table->text('transaction_items')->nullable();

            $table->text('origin_district')->nullable();
            $table->text('destination_district')->nullable();

            $table->string('bank_account_num')->nullable();
            $table->string('do_number')->nullable();
            $table->date('do_date')->nullable();
            $table->date('do_actual_date')->nullable();

            $table->text('dest_address')->nullable();
            $table->text('customer_name')->nullable();

            $table->text('note')->nullable();

            // 'SUBMITTED', 'DONE', 'CANCELLED', 'REJECTED', 'CANCELLED NO REFUND', 'CANCELLED AND REFUND', 'DONE AND WAITING_DOCUMENT'
            $table->text('status')->nullable();

            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignUuid('customer_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('customers')
                  ->nullOnDelete();
            $table->foreignUuid('trip_price_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('trip_prices')
                  ->nullOnDelete();
            $table->foreignUuid('origin_sub_district_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('sub_districts')
                  ->nullOnDelete();
            $table->foreignUuid('dest_sub_district_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('sub_districts')
                  ->nullOnDelete();
            $table->foreignUuid('vehicle_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('vehicles')
                  ->nullOnDelete();
            $table->foreignUuid('bank_account_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('bank_accounts')
                  ->nullOnDelete();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
