<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEntPhysicalTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entPhysical', function (Blueprint $table) {
            $table->increments('entPhysical_id');
            $table->unsignedInteger('device_id')->index('device_id');
            $table->integer('entPhysicalIndex');
            $table->text('entPhysicalDescr', 65535);
            $table->text('entPhysicalClass', 65535);
            $table->text('entPhysicalName', 65535);
            $table->string('entPhysicalHardwareRev', 64)->nullable();
            $table->string('entPhysicalFirmwareRev', 64)->nullable();
            $table->string('entPhysicalSoftwareRev', 64)->nullable();
            $table->string('entPhysicalAlias', 32)->nullable();
            $table->string('entPhysicalAssetID', 32)->nullable();
            $table->string('entPhysicalIsFRU', 8)->nullable();
            $table->text('entPhysicalModelName', 65535);
            $table->text('entPhysicalVendorType', 65535)->nullable();
            $table->text('entPhysicalSerialNum', 65535);
            $table->integer('entPhysicalContainedIn');
            $table->integer('entPhysicalParentRelPos');
            $table->text('entPhysicalMfgName', 65535);
            $table->integer('ifIndex')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('entPhysical');
    }
}
