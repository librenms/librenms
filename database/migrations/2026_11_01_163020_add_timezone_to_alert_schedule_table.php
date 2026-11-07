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
        Schema::table('alert_schedule', function (Blueprint $table): void {
            $table->string('timezone', 64)->default('UTC')->after('end');
        });

        // existing schedules were entered and evaluated using the server timezone
        DB::table('alert_schedule')->update(['timezone' => config('app.timezone')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alert_schedule', function (Blueprint $table): void {
            $table->dropColumn('timezone');
        });
    }
};
