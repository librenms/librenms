<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // add inserted column after device id with a default of current_timestamp
            $table->timestamp('inserted')->nullable()->default(null)->after('device_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // revert add inserted column after device id with a default of current_timestamp
            $table->dropColumn('inserted');
        });
    }
};
