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
        Schema::create('districts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestampsTz();
            $table->timestampTz('deleted_at')->nullable();

            // $table->foreignUuid('city_id')
            //       ->nullable() // Keep nullable so existing users don't break during migration
            //       ->constrained('cities')
            //       ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
