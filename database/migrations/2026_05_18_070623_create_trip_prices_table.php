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
        Schema::create('trip_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('base_price')->nullable();
            $table->timestampsTz();
            $table->timestampTz('deleted_at')->nullable();

            $table->foreignUuid('customer_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('customers')
                  ->nullOnDelete(); 
            $table->foreignUuid('origin_sub_district_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('sub_districts')
                  ->nullOnDelete(); 
            $table->foreignUuid('dest_sub_district_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('sub_districts')
                  ->nullOnDelete(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_prices');
    }
};
