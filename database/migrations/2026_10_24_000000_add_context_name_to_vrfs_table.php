<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vrfs', function (Blueprint $table): void {
            $table->string('context_name', 128)->nullable()->after('vrf_name');
            $table->boolean('context_manual')->default(false)->after('context_name');
            $table->boolean('vrf_manual')->default(false)->after('context_manual');
            $table->boolean('context_verified')->default(false)->after('vrf_manual');
            $table->unsignedTinyInteger('context_failures')->default(0)->after('context_verified');
            $table->boolean('context_enabled')->default(true)->after('context_verified');
            $table->index(['device_id', 'context_name']);
        });
    }

    public function down(): void
    {
        Schema::table('vrfs', function (Blueprint $table): void {
            $table->dropIndex(['device_id', 'context_name']);
            $table->dropColumn(['context_name', 'context_manual', 'vrf_manual', 'context_verified', 'context_failures', 'context_enabled']);
        });
    }
};
