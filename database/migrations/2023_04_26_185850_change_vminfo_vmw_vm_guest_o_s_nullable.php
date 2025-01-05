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
    public function up()
    {
        Schema::table('vminfo', function (Blueprint $table) {
            $table->string('vmwVmGuestOS', 128)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vminfo', function (Blueprint $table) {
            $table->string('vmwVmGuestOS', 128)->nullable(false)->change();
        });
    }
};
