<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImprovePortsSearchIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->index(['ifAlias', 'port_descr_descr', 'portName']);
            $table->index(['ifDescr', 'ifName']);
            $table->dropIndex('ports_ifdescr_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->index('ifDescr');
            $table->dropIndex('ports_ifalias_port_descr_descr_portname_index');
            $table->dropIndex('ports_ifdescr_ifname_index');
        });
    }
}
