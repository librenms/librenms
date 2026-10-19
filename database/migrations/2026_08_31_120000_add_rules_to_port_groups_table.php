<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('port_groups', function (Blueprint $table): void {
            // default to static so existing groups keep their manually assigned ports
            $table->string('type', 16)->default('static')->after('desc');
            $table->text('rules')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        if (LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('port_groups', function (Blueprint $table): void {
                $table->dropColumn(['type', 'rules']);
            });
        }
    }
};
