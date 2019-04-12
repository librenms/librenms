<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersAuthIdVarchar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_id', 128)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // remove non-numeric auth_ids
            DB::table('users')->where('auth_id', 'not regexp', '^[0-9]+$')->update(['auth_id' => null]);
            $table->integer('auth_id')->change();
        });
    }
}
