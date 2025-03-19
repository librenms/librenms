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
    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->index('ifDescr');
            $table->dropIndex('ports_ifalias_port_descr_descr_portname_index');
            $table->dropIndex('ports_ifdescr_ifname_index');
        });
    }
};
