<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete the old index
        DB::statement('DROP INDEX IF EXISTS transaction_details_amount_unique_number');

        // Re-create the index with the soft delete check
        DB::statement("
            CREATE UNIQUE INDEX transaction_details_amount_unique_number
            ON transaction_details (amount_unique_number)
            WHERE status IN ('SUBMITTED', 'APPROVED') AND deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the old index without soft delete check
        DB::statement('DROP INDEX IF EXISTS transaction_details_amount_unique_number');

        DB::statement("
            CREATE UNIQUE INDEX transaction_details_amount_unique_number
            ON transaction_details (amount_unique_number)
            WHERE status IN ('SUBMITTED', 'APPROVED')
        ");
    }
};
