<?php

use Illuminate\Database\Migrations\Migration;

class MigrateEmptyUserFuncsToNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\Sensor::where('user_func', '')->update(['user_func' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
