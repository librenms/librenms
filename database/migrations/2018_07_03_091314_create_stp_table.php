<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stp', function (Blueprint $table) {
            $table->increments('stp_id');
            $table->unsignedInteger('device_id')->index('stp_host');
            $table->boolean('rootBridge');
            $table->string('bridgeAddress', 32);
            $table->string('protocolSpecification', 16);
            $table->mediumInteger('priority');
            $table->string('timeSinceTopologyChange', 32);
            $table->mediumInteger('topChanges');
            $table->string('designatedRoot', 32);
            $table->mediumInteger('rootCost');
            $table->integer('rootPort')->nullable();
            $table->mediumInteger('maxAge');
            $table->mediumInteger('helloTime');
            $table->mediumInteger('holdTime');
            $table->mediumInteger('forwardDelay');
            $table->smallInteger('bridgeMaxAge');
            $table->smallInteger('bridgeHelloTime');
            $table->smallInteger('bridgeForwardDelay');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('stp');
    }
}
