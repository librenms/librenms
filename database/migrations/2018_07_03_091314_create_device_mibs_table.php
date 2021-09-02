<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeviceMibsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_mibs', function (Blueprint $table) {
            $table->unsignedInteger('device_id');
            $table->string('module');
            $table->string('mib');
            $table->string('included_by');
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('last_modified')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('last_modified')->useCurrent();
            }
            $table->primary(['device_id', 'module', 'mib']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('device_mibs');
    }
}
