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
        if (LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('ipv4_mac', function (Blueprint $table) {
                $table->string('context_name', 128)->nullable()->change();
            });
        }
    }
};
