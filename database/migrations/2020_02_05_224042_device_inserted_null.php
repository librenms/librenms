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
        Schema::table('devices', function (Blueprint $table) {
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                \DB::statement('ALTER TABLE `devices` CHANGE `inserted` `inserted` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;');
            } else {
                $table->dateTime('inserted')->nullable()->useCurrent()->change();
            }
        });

        DB::table('devices')->update(['inserted' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
