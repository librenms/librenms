<?php

use Illuminate\Database\Migrations\Migration;

class DisableExamplePlugin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // disable ExamplePlugin that was accidentally enabled for everyone
        DB::table('plugins')
            ->where([
                'plugin_name' => 'ExamplePlugin',
                'version' => 2,
            ])->update([
                'plugin_active' => 0,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
