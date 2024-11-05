<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mpls_sdps', function ($table) {
            // drop and recreate because of SQLite not accepting any changes.
            // data is collected again next poll anyway.
            $table->dropColumn(['sdpFarEndInetAddressType']);
        });
        Schema::table('mpls_sdps', function ($table) {
            $table->enum('sdpFarEndInetAddressType', ['unknown', 'ipv4', 'ipv6', 'ipv4z', 'ipv6z', 'dns'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mpls_sdps', function ($table) {
            $table->dropColumn(['sdpFarEndInetAddressType']);
        });
        Schema::table('mpls_sdps', function ($table) {
            $table->enum('sdpFarEndInetAddressType', ['ipv4', 'ipv6'])->nullable();
        });
    }
};
