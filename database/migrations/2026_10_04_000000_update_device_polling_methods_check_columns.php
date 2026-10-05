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
        Schema::table('device_polling_methods', function (Blueprint $table): void {
            $table->text('last_check_message')->nullable()->after('last_check_successful');
            $table->timestamp('last_check_changed_at')->nullable()->after('last_check_message');
        });

        Schema::table('device_polling_methods', function (Blueprint $table): void {
            $table->dropColumn('last_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_polling_methods', function (Blueprint $table): void {
            $table->timestamp('last_checked_at')->nullable()->after('settings');
        });

        Schema::table('device_polling_methods', function (Blueprint $table): void {
            $table->dropColumn(['last_check_message', 'last_check_changed_at']);
        });
    }
};
