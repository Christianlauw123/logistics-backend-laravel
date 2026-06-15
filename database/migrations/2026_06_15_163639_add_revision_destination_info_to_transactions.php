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
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('revision_trip_price_amount')->nullable();
            $table->text('revision_destination_district')->nullable();
            $table->foreignUuid('revision_dest_sub_district_id')
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
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['revision_dest_sub_district_id']);
            $table->dropColumn(['revision_trip_price_amount', 'revision_dest_sub_district_id', 'revision_destination_district']);
        });
    }
};
