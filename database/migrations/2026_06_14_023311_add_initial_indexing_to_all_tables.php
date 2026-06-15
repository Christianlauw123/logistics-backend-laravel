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
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->unique('account_identifier_number');
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('sub_districts', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('trip_prices', function (Blueprint $table) {
            $table->index(['customer_id', 'origin_sub_district_id']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropUnique('account_identifier_number');
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->dropIndex('name');
        });

        Schema::table('sub_districts', function (Blueprint $table) {
            $table->dropIndex('name');
        });

        Schema::table('trip_prices', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'origin_sub_district_id']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('name');
        });
    }
};
