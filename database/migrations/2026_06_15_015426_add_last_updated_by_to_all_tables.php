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
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('trip_prices', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('sub_districts', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
                  ->nullOnDelete();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->foreignUuid('last_updated_by_id')
                  ->nullable() // Keep nullable so existing users don't break during migration
                  ->constrained('users')
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
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['last_updated_by_id', 'user_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropColumn(['last_updated_by_id']);
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropColumn(['last_updated_by_id']);
        });

        Schema::table('trip_prices', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['last_updated_by_id', 'user_id']);
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropColumn(['last_updated_by_id']);
        });


        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['last_updated_by_id', 'user_id']);
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['last_updated_by_id', 'user_id']);
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['last_updated_by_id', 'user_id']);
        });

        Schema::table('sub_districts', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['last_updated_by_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['last_updated_by_id', 'user_id']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['last_updated_by_id', 'user_id']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['last_updated_by_id', 'user_id']);
        });
    }
};
