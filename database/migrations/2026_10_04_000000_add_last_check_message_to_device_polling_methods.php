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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_polling_methods', function (Blueprint $table): void {
            $table->dropColumn('last_check_message');
        });
    }
};
