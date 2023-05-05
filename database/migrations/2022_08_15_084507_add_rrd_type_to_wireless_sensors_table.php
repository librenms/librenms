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
    public function up()
    {
        Schema::table('wireless_sensors', function (Blueprint $table) {
            $table->enum('rrd_type', ['GAUGE', 'COUNTER', 'DERIVE', 'DCOUNTER', 'DDERIVE', 'ABSOLUTE'])->after('access_point_id')->default('GAUGE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wireless_sensors', function (Blueprint $table) {
            $table->dropColumn('rrd_type');
        });
    }
};
