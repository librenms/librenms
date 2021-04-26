<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeviceOidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_oids', function (Blueprint $table) {
            $table->unsignedInteger('device_id');
            $table->string('oid');
            $table->string('module');
            $table->string('mib');
            $table->string('object_type');
            $table->string('value')->nullable();
            $table->bigInteger('numvalue')->nullable();
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('last_modified')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('last_modified')->useCurrent();
            }
            $table->primary(['device_id', 'oid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('device_oids');
    }
}
