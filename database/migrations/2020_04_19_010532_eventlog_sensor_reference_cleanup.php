<?php

use Illuminate\Database\Migrations\Migration;
use LibreNMS\Enum\SensorClass;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach (SensorClass::all() as $type) {
            DB::table('eventlog')->where('type', ucfirst($type))->update(['type' => $type]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        foreach (SensorClass::all() as $type) {
            DB::table('eventlog')->where('type', $type)->update(['type' => ucfirst($type)]);
        }
    }
};
