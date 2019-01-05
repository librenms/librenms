<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProxmoxTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxmox', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id')->default(0);
            $table->integer('vmid');
            $table->string('cluster');
            $table->string('description')->nullable();
            $table->timestamp('last_seen')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unique(['cluster','vmid'], 'cluster_vm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('proxmox');
    }
}
