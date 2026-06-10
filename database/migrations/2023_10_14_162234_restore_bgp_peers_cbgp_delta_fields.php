<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bgpPeers_cbgp', function (Blueprint $table) {
            $table->integer('AcceptedPrefixes_delta')->change();
            $table->integer('DeniedPrefixes_delta')->change();
            $table->integer('AdvertisedPrefixes_delta')->change();
            $table->integer('SuppressedPrefixes_delta')->change();
            $table->integer('WithdrawnPrefixes_delta')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
