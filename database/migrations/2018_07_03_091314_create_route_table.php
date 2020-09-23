<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('route', function (Blueprint $table) {
            $table->unsignedInteger('device_id');
            $table->string('context_name', 128);
            $table->string('ipRouteDest', 39);
            $table->string('ipRouteIfIndex', 256)->nullable();
            $table->string('ipRouteMetric', 256);
            $table->string('ipRouteNextHop', 39);
            $table->string('ipRouteType', 256);
            $table->string('ipRouteProto', 256);
            $table->unsignedInteger('discoveredAt');
            $table->string('ipRouteMask', 256);
            $table->index(['device_id', 'context_name', 'ipRouteDest', 'ipRouteNextHop'], 'device');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('route');
    }
}
