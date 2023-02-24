<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIfaceFieldToBgpPeers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bgpPeers', function (Blueprint $table) {
            $table->unsignedInteger('bgpPeerIface')->nullable()->after('bgpPeerLastErrorText');
        });
    }

    /**
     * Reverse the migrations.
     *
     *
     *
     *
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bgpPeers', function (Blueprint $table) {
            $table->dropColumn(['bgpPeerIface']);
        });
    }
}
