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
        Schema::table('ipv6_addresses', function (Blueprint $table) {
            $table->integer('device_id')->after('ipv6_address_id')->nullable()->unsigned()->index();
            $table->string('ipv6_network', 64)->after('ipv6_prefixlen');
            $table->dropColumn('ipv6_network_id');
            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipv6_addresses', function (Blueprint $table) {
            $table->dropForeign('ipv6_addresses_device_id_foreign');
            $table->dropColumn(['device_id', 'ipv6_network']);
            $table->string('ipv6_network_id', 128)->after('ipv6_origin');
        });
    }
};
