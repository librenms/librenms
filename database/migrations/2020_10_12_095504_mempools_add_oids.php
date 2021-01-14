<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MempoolsAddOids extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mempools', function (Blueprint $table) {
            $table->dropColumn('hrDeviceIndex');
            $table->string('mempool_class', 32)->default('system')->after('mempool_type');
            $table->string('mempool_descr', 128)->change();
            $table->string('mempool_perc_oid')->after('mempool_perc')->nullable();
            $table->string('mempool_used_oid')->after('mempool_used')->nullable();
            $table->string('mempool_free_oid')->after('mempool_free')->nullable();
            $table->string('mempool_total_oid')->after('mempool_total')->nullable();
        });

        // rediscover mempools to fill empty columns and prevent gaps
        DB::table('devices')->whereIn('device_id', function ($query) {
            $query->from('mempools')->distinct()->select('device_id');
        })->update(['last_discovered' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mempools', function (Blueprint $table) {
            $table->string('mempool_descr', 64)->change();
            $table->integer('hrDeviceIndex')->nullable()->after('entPhysicalIndex');
            $table->dropColumn(['mempool_class', 'mempool_perc_oid', 'mempool_used_oid', 'mempool_free_oid', 'mempool_total_oid']);
        });
    }
}
