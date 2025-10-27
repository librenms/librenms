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
            $table->unsignedBigInteger('AcceptedPrefixes')->change();
            $table->unsignedBigInteger('DeniedPrefixes')->change();
            $table->unsignedBigInteger('PrefixAdminLimit')->change();
            $table->unsignedBigInteger('PrefixThreshold')->change();
            $table->unsignedBigInteger('PrefixClearThreshold')->change();
            $table->unsignedBigInteger('AdvertisedPrefixes')->change();
            $table->unsignedBigInteger('SuppressedPrefixes')->change();
            $table->unsignedBigInteger('WithdrawnPrefixes')->change();
            $table->bigInteger('AcceptedPrefixes_delta')->change();
            $table->unsignedBigInteger('AcceptedPrefixes_prev')->change();
            $table->bigInteger('DeniedPrefixes_delta')->change();
            $table->unsignedBigInteger('DeniedPrefixes_prev')->change();
            $table->bigInteger('AdvertisedPrefixes_delta')->change();
            $table->unsignedBigInteger('AdvertisedPrefixes_prev')->change();
            $table->bigInteger('SuppressedPrefixes_delta')->change();
            $table->unsignedBigInteger('SuppressedPrefixes_prev')->change();
            $table->bigInteger('WithdrawnPrefixes_delta')->change();
            $table->unsignedBigInteger('WithdrawnPrefixes_prev')->change();
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
            $table->integer('AcceptedPrefixes')->unsigned()->change();
            $table->integer('DeniedPrefixes')->unsigned()->change();
            $table->integer('PrefixAdminLimit')->unsigned()->change();
            $table->integer('PrefixThreshold')->unsigned()->change();
            $table->integer('PrefixClearThreshold')->unsigned()->change();
            $table->integer('AdvertisedPrefixes')->unsigned()->change();
            $table->integer('SuppressedPrefixes')->unsigned()->change();
            $table->integer('WithdrawnPrefixes')->unsigned()->change();
            $table->integer('AcceptedPrefixes_delta')->change();
            $table->integer('AcceptedPrefixes_prev')->unsigned()->change();
            $table->integer('DeniedPrefixes_delta')->change();
            $table->integer('DeniedPrefixes_prev')->unsigned()->change();
            $table->integer('AdvertisedPrefixes_delta')->change();
            $table->integer('AdvertisedPrefixes_prev')->unsigned()->change();
            $table->integer('SuppressedPrefixes_delta')->change();
            $table->integer('SuppressedPrefixes_prev')->unsigned()->change();
            $table->integer('WithdrawnPrefixes_delta')->change();
            $table->integer('WithdrawnPrefixes_prev')->unsigned()->change();
        });
    }
};
