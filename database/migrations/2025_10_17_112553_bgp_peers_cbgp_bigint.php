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
        Schema::table('bgpPeers_cbgp', function (Blueprint $table) {
            $table->bigInteger('AcceptedPrefixes')->change();
            $table->bigInteger('DeniedPrefixes')->change();
            $table->bigInteger('PrefixAdminLimit')->change();
            $table->bigInteger('PrefixThreshold')->change();
            $table->bigInteger('PrefixClearThreshold')->change();
            $table->bigInteger('AdvertisedPrefixes')->change();
            $table->bigInteger('SuppressedPrefixes')->change();
            $table->bigInteger('WithdrawnPrefixes')->change();
            $table->bigInteger('AcceptedPrefixes_delta')->change();
            $table->bigInteger('AcceptedPrefixes_prev')->change();
            $table->bigInteger('DeniedPrefixes_delta')->change();
            $table->bigInteger('DeniedPrefixes_prev')->change();
            $table->bigInteger('AdvertisedPrefixes_delta')->change();
            $table->bigInteger('AdvertisedPrefixes_prev')->change();
            $table->bigInteger('SuppressedPrefixes_delta')->change();
            $table->bigInteger('SuppressedPrefixes_prev')->change();
            $table->bigInteger('WithdrawnPrefixes_delta')->change();
            $table->bigInteger('WithdrawnPrefixes_prev')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
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
