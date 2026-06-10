<?php

use App\Models\Sensor;
use Illuminate\Database\Migrations\Migration;
use LibreNMS\Enum\Sensor as SensorEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Sensor::whereNotIn('sensor_class', SensorEnum::values())->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
