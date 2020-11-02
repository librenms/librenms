<?php

use App\Models\Vminfo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Enum\PowerState;

class AddPowerstateEnumToVminfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Vminfo::select('id', 'vmwVmState')->get() as $vm) {
            if (is_numeric($vm->vmwVmState)) {
                continue;
            }

            $vm->vmwVmState = PowerState::STATES[strtolower($vm->vmwVmState)];
            $vm->update();
        }

        // No native support for tinyints apparently.
        DB::statement('ALTER TABLE `vminfo` CHANGE `vmwVmState` `vmwVmState` TINYINT UNSIGNED NOT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vminfo', function (Blueprint $table) {
            $table->string('vmwVmState', 128)->change();
        });
    }
}
