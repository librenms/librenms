<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ipv4_addresses', function (Blueprint $table): void {
            $table->index('ipv4_address');
        });

        Schema::table('ipv6_addresses', function (Blueprint $table): void {
            $table->index('ipv6_address');
        });
    }

    public function down(): void
    {
        Schema::table('ipv6_addresses', function (Blueprint $table): void {
            $table->dropIndex(['ipv6_address']);
        });

        Schema::table('ipv4_addresses', function (Blueprint $table): void {
            $table->dropIndex(['ipv4_address']);
        });
    }
};
