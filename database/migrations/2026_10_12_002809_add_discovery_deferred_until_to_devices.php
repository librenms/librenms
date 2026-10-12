<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Discovery attempts that did not complete (device down or error) hold off the next attempt until this time.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table): void {
            $table->timestamp('discovery_deferred_until')->nullable()->after('last_discovered');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table): void {
            $table->dropColumn('discovery_deferred_until');
        });
    }
};
