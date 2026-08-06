<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('mpls_saps', function (Blueprint $table) {
            $table->unsignedBigInteger('sapIngressOctets')->nullable()->after('sapLastStatusChange');
            $table->unsignedBigInteger('sapEgressOctets')->nullable()->after('sapIngressOctets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('mpls_saps', function (Blueprint $table) {
            $table->dropColumn(['sapIngressOctets', 'sapEgressOctets']);
        });
    }
};
