<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastErrorFieldsToBgpPeers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bgpPeers', function (Blueprint $table) {
            $table->integer('bgpPeerLastErrorCode')->nullable()->after('bgpPeerAdminStatus');
            $table->integer('bgpPeerLastErrorSubCode')->nullable()->after('bgpPeerLastErrorCode');
            $table->string('bgpPeerLastErrorText', 254)->nullable()->after('bgpPeerLastErrorSubCode');
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
            $table->dropColumn(['bgpPeerLastErrorCode', 'bgpPeerLastErrorSubCode', 'bgpPeerLastErrorText']);
        });
    }
}
