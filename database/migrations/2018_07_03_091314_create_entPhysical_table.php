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
            $table->unsignedInteger('device_id')->index();
            $table->integer('entPhysicalIndex');
            $table->text('entPhysicalDescr');
            $table->text('entPhysicalClass');
            $table->text('entPhysicalName');
            $table->string('entPhysicalHardwareRev', 64)->nullable();
            $table->string('entPhysicalFirmwareRev', 64)->nullable();
            $table->string('entPhysicalSoftwareRev', 64)->nullable();
            $table->string('entPhysicalAlias', 32)->nullable();
            $table->string('entPhysicalAssetID', 32)->nullable();
            $table->string('entPhysicalIsFRU', 8)->nullable();
            $table->text('entPhysicalModelName');
            $table->text('entPhysicalVendorType')->nullable();
            $table->text('entPhysicalSerialNum');
            $table->integer('entPhysicalContainedIn');
            $table->integer('entPhysicalParentRelPos');
            $table->text('entPhysicalMfgName');
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
