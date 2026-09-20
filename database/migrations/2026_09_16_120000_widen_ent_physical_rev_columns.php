<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ENTITY-MIB defines the three revision objects as SnmpAdminString, which allows 255
     * octets. The columns held 96, so a device that reports a longer string aborted the
     * entity-physical update with a data too long error on every discovery and poll.
     */
    private const COLUMNS = [
        'entPhysicalHardwareRev',
        'entPhysicalFirmwareRev',
        'entPhysicalSoftwareRev',
    ];

    public function up(): void
    {
        Schema::table('entPhysical', function (Blueprint $table): void {
            foreach (self::COLUMNS as $column) {
                $table->string($column, 255)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('entPhysical', function (Blueprint $table): void {
            foreach (self::COLUMNS as $column) {
                $table->string($column, 96)->nullable()->change();
            }
        });
    }
};
