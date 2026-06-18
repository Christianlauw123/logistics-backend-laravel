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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('capacity', 24, 2)->nullable()->change();
        });

        Schema::table('trip_prices', function (Blueprint $table) {
            $table->decimal('base_price', 24, 2)->nullable()->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('trip_price_amount', 24, 2)->nullable()->change();
            $table->decimal('vehicle_capacity', 24, 2)->nullable()->change();
            $table->decimal('transaction_capacity', 24, 2)->nullable()->change();
            $table->decimal('revision_trip_price_amount', 24, 2)->nullable()->change();
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->decimal('amount', 24, 2)->nullable()->change();
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->decimal('amount', 24, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('capacity')->nullable()->change();
        });

        Schema::table('trip_prices', function (Blueprint $table) {
            $table->decimal('base_price')->nullable()->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('trip_price_amount')->nullable()->change();
            $table->decimal('vehicle_capacity')->nullable()->change();
            $table->decimal('transaction_capacity')->nullable()->change();
            $table->decimal('revision_trip_price_amount')->nullable()->change();
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->decimal('amount')->nullable()->change();
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->decimal('amount')->nullable()->change();
        });
    }
};
