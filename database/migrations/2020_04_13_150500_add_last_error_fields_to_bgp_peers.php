<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
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
    public function down(): void
    {
        Schema::table('bgpPeers', function (Blueprint $table) {
            $table->dropColumn(['bgpPeerLastErrorCode', 'bgpPeerLastErrorSubCode', 'bgpPeerLastErrorText']);
        });
    }
};
