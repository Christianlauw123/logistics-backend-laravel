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
        Schema::table('trip_prices', function (Blueprint $table) {
            $table->decimal('weight_category')->default(0);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('weight_category')->default(0);
            $table->decimal('revision_weight_category')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_prices', function (Blueprint $table) {
            $table->dropColumn(['weight_category']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['weight_category', 'revision_weight_category']);
        });
    }
};
