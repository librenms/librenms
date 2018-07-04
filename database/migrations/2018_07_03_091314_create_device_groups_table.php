<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeviceGroupsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->unique('name');
            $table->string('desc')->default('');
            $table->text('pattern', 65535)->nullable();
            $table->text('params', 65535)->nullable();
        });

        \DB::statement("ALTER TABLE `device_groups` CHANGE `id` `id` int(11) unsigned NOT NULL auto_increment;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('device_groups');
    }
}
