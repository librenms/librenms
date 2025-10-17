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
            $table->unsignedInteger('device_id');
            $table->string('bgpPeerIdentifier', 64);
            $table->string('afi', 16);
            $table->string('safi', 16);
            $table->bigInteger('AcceptedPrefixes');
            $table->bigInteger('DeniedPrefixes');
            $table->bigInteger('PrefixAdminLimit');
            $table->bigInteger('PrefixThreshold');
            $table->bigInteger('PrefixClearThreshold');
            $table->bigInteger('AdvertisedPrefixes');
            $table->bigInteger('SuppressedPrefixes');
            $table->bigInteger('WithdrawnPrefixes');
            $table->bigInteger('AcceptedPrefixes_delta');
            $table->bigInteger('AcceptedPrefixes_prev');
            $table->bigInteger('DeniedPrefixes_delta');
            $table->bigInteger('DeniedPrefixes_prev');
            $table->bigInteger('AdvertisedPrefixes_delta');
            $table->bigInteger('AdvertisedPrefixes_prev');
            $table->bigInteger('SuppressedPrefixes_delta');
            $table->bigInteger('SuppressedPrefixes_prev');
            $table->bigInteger('WithdrawnPrefixes_delta');
            $table->bigInteger('WithdrawnPrefixes_prev');
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
    public function down(): void
    {
        Schema::table('bgpPeers_cbgp', function (Blueprint $table) {
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
};
