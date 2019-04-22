<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PortsFdb;
use Carbon\Carbon;

class UpdateRouteTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inetCidrRoute', function (Blueprint $table) {
              $table->increments('inetCidrRoute_id');
              $table->timestamps();
              $table->unsignedInteger('device_id');
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
              $table->string('inetCidrRouteNextHop_device_id');
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
        Schema::drop('inetCidrRoute');
    }
}
