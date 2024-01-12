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
            $table->integer('AcceptedPrefixes')->unsigned()->change();
            $table->integer('DeniedPrefixes')->unsigned()->change();
            $table->integer('PrefixAdminLimit')->unsigned()->change();
            $table->integer('PrefixThreshold')->unsigned()->change();
            $table->integer('PrefixClearThreshold')->unsigned()->change();
            $table->integer('AdvertisedPrefixes')->unsigned()->change();
            $table->integer('SuppressedPrefixes')->unsigned()->change();
            $table->integer('WithdrawnPrefixes')->unsigned()->change();
            $table->integer('AcceptedPrefixes_prev')->unsigned()->change();
            $table->integer('DeniedPrefixes_prev')->unsigned()->change();
            $table->integer('AdvertisedPrefixes_prev')->unsigned()->change();
            $table->integer('SuppressedPrefixes_prev')->unsigned()->change();
            $table->integer('WithdrawnPrefixes_prev')->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bgpPeers_cbgp', function (Blueprint $table) {
            $table->integer('AcceptedPrefixes')->change();
            $table->integer('DeniedPrefixes')->change();
            $table->integer('PrefixAdminLimit')->change();
            $table->integer('PrefixThreshold')->change();
            $table->integer('PrefixClearThreshold')->change();
            $table->integer('AdvertisedPrefixes')->change();
            $table->integer('SuppressedPrefixes')->change();
            $table->integer('WithdrawnPrefixes')->change();
            $table->integer('AcceptedPrefixes_delta')->change();
            $table->integer('AcceptedPrefixes_prev')->change();
            $table->integer('DeniedPrefixes_delta')->change();
            $table->integer('DeniedPrefixes_prev')->change();
            $table->integer('AdvertisedPrefixes_delta')->change();
            $table->integer('AdvertisedPrefixes_prev')->change();
            $table->integer('SuppressedPrefixes_delta')->change();
            $table->integer('SuppressedPrefixes_prev')->change();
            $table->integer('WithdrawnPrefixes_delta')->change();
            $table->integer('WithdrawnPrefixes_prev')->change();
        });
    }
};
