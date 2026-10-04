<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table): void {
            $table->unsignedBigInteger('ingress_speed')->nullable();
            $table->unsignedBigInteger('egress_speed')->nullable();
        });

        DB::table('ports')->update([
            'ingress_speed' => DB::raw('ifSpeed'),
            'egress_speed' => DB::raw('ifSpeed'),
        ]);
    }

    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table): void {
            $table->dropColumn(['ingress_speed', 'egress_speed']);
        });
    }
};
