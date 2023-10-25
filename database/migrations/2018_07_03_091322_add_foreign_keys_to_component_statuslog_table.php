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
        Schema::table('component_statuslog', function (Blueprint $table) {
            $table->foreign('component_id', 'component_statuslog_ibfk_1')->references('id')->on('component')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (\LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('component_statuslog', function (Blueprint $table) {
                $table->dropForeign('component_statuslog_ibfk_1');
            });
        }
    }
};
