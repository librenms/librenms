<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersPrefsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_prefs', function (Blueprint $table) {
            $table->integer('user_id')->primary();
            $table->string('pref', 32)->index();
            $table->string('value', 128);
            $table->unique(['user_id','pref']);
        });

        \DB::statement("ALTER TABLE `users_prefs` CHANGE `user_id` `user_id` int(16) NOT NULL ;");
        \DB::statement("ALTER TABLE `users_prefs` ADD UNIQUE `user_id.pref` (`user_id`,`pref`);");
        \DB::statement("ALTER TABLE `users_prefs` DROP INDEX `PRIMARY`;");
        \DB::statement("ALTER TABLE `users_prefs` DROP INDEX `users_prefs_user_id_pref_unique`;");
        \DB::statement("ALTER TABLE `users_prefs` DROP INDEX `users_prefs_pref_index`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_prefs');
    }
}
