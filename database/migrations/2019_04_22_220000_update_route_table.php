<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Remove the old route table, as it is not used anymore.
        Schema::drop('route');

        Schema::create('route', function (Blueprint $table) {
            $table->increments('route_id');
            $table->timestamps();
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('port_id');
            $table->string('context_name')->nullable();
            $table->bigInteger('inetCidrRouteIfIndex');
            $table->unsignedInteger('inetCidrRouteType');
            $table->unsignedInteger('inetCidrRouteProto');
            $table->unsignedInteger('inetCidrRouteNextHopAS');
            $table->unsignedInteger('inetCidrRouteMetric1');
            $table->string('inetCidrRouteDestType');
            $table->string('inetCidrRouteDest');
            $table->string('inetCidrRouteNextHopType');
            $table->string('inetCidrRouteNextHop');
            $table->string('inetCidrRoutePolicy');
            $table->unsignedInteger('inetCidrRoutePfxLen');
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
        // Create the old route table to reverse this.
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
}
