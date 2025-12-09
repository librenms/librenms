<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ports_voice_vlan', function (Blueprint $table) {
            $table->increments('ports_voice_vlan_id');
            $table->integer('port_id')->unsigned()->default(0);
            $table->integer('device_id')->unsigned();
            $table->integer('voice_vlan')->unsigned()->default(0);
            $table->unique(['device_id', 'port_id', 'voice_vlan'], 'ports_voice_vlans_device_id_port_id_voice_vlan_unique');
            $table->index(['port_id'], 'ports_voice_vlans_port_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('ports_voice_vlan');
    }
};
