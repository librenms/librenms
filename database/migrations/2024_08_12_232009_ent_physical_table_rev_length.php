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
        Schema::table('entPhysical', function (Blueprint $table) {
            $table->string('entPhysicalHardwareRev', 96)->nullable()->change();
            $table->string('entPhysicalFirmwareRev', 96)->nullable()->change();
            $table->string('entPhysicalSoftwareRev', 96)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entPhysical', function (Blueprint $table) {
            $table->string('entPhysicalHardwareRev', 64)->nullable()->change();
            $table->string('entPhysicalFirmwareRev', 64)->nullable()->change();
            $table->string('entPhysicalSoftwareRev', 64)->nullable()->change();
        });
    }
};
