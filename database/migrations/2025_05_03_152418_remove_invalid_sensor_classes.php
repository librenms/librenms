<?php

use App\Models\Sensor;
use Illuminate\Database\Migrations\Migration;
use LibreNMS\Enum\SensorType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Sensor::whereNotIn('sensor_class', SensorType::values())->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
