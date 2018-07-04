<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLinksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('local_port_id')->nullable()->index('src_if');
            $table->integer('local_device_id');
            $table->integer('remote_port_id')->nullable()->index('dst_if');
            $table->boolean('active')->default(1);
            $table->string('protocol', 11)->nullable();
            $table->string('remote_hostname', 128);
            $table->integer('remote_device_id');
            $table->string('remote_port', 128);
            $table->string('remote_platform', 256)->nullable();
            $table->string('remote_version', 256);
            $table->index(['local_device_id','remote_device_id'], 'local_device_id');
        });

        \DB::statement("ALTER TABLE `links` CHANGE `active` `active` tinyint(4) NOT NULL DEFAULT '1' ;");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('links');
    }
}
