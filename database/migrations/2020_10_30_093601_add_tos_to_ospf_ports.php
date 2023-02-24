<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTosToOspfPorts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ospf_ports', function (Blueprint $table) {
            $table->string('ospfIfMetricIpAddress', 32)->nullable()->after('ospfIfAuthType');
            $table->integer('ospfIfMetricAddressLessIf')->nullable()->after('ospfIfMetricIpAddress');
            $table->integer('ospfIfMetricTOS')->nullable()->after('ospfIfMetricAddressLessIf');
            $table->integer('ospfIfMetricValue')->nullable()->after('ospfIfMetricTOS');
            $table->string('ospfIfMetricStatus', 32)->nullable()->after('ospfIfMetricValue');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ospf_ports', function (Blueprint $table) {
            $table->dropColumn(['ospfIfMetricIpAddress', 'ospfIfMetricAddressLessIf', 'ospfIfMetricTOS', 'ospfIfMetricValue', 'ospfIfMetricStatus']);
        });
    }
}
