<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBgpPeersCbgpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bgpPeers_cbgp', function (Blueprint $table) {
            $table->unsignedInteger('device_id');
            $table->string('bgpPeerIdentifier', 64);
            $table->string('afi', 16);
            $table->string('safi', 16);
            $table->integer('AcceptedPrefixes');
            $table->integer('DeniedPrefixes');
            $table->integer('PrefixAdminLimit');
            $table->integer('PrefixThreshold');
            $table->integer('PrefixClearThreshold');
            $table->integer('AdvertisedPrefixes');
            $table->integer('SuppressedPrefixes');
            $table->integer('WithdrawnPrefixes');
            $table->integer('AcceptedPrefixes_delta');
            $table->integer('AcceptedPrefixes_prev');
            $table->integer('DeniedPrefixes_delta');
            $table->integer('DeniedPrefixes_prev');
            $table->integer('AdvertisedPrefixes_delta');
            $table->integer('AdvertisedPrefixes_prev');
            $table->integer('SuppressedPrefixes_delta');
            $table->integer('SuppressedPrefixes_prev');
            $table->integer('WithdrawnPrefixes_delta');
            $table->integer('WithdrawnPrefixes_prev');
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'bgpPeerIdentifier', 'afi', 'safi']);
            $table->index(['device_id', 'bgpPeerIdentifier', 'context_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bgpPeers_cbgp');
    }
}
