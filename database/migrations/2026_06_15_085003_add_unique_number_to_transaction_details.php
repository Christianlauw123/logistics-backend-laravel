<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->unsignedSmallInteger('amount_unique_number')->nullable();
        });

        DB::statement("CREATE UNIQUE INDEX transaction_details_amount_unique_number ON transaction_details (amount_unique_number) WHERE status IN ('SUBMITTED', 'APPROVED')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS transaction_details_amount_unique_number');
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn(['amount_unique_number']);
        });
    }
};
