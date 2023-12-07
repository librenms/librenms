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
        DB::statement("ALTER TABLE mpls_sdps MODIFY COLUMN sdpFarEndInetAddressType ENUM('unknown', 'ipv4', 'ipv6', 'ipv4z', 'ipv6z', 'dns')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mpls_sdps MODIFY COLUMN sdpFarEndInetAddressType ENUM('ipv4', 'ipv6')");
    }
};
