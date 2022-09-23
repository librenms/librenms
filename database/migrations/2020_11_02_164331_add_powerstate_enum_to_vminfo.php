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
        Vminfo::select('id', 'vmwVmState')->chunk(100, function ($vms) {
            foreach ($vms as $vm) {
                if (is_numeric($vm->vmwVmState)) {
                    continue;
                }

                $vm->vmwVmState = PowerState::STATES[strtolower($vm->vmwVmState)] ?? PowerState::UNKNOWN;
                $vm->update();
            }
        });

        Schema::table('vminfo', function (Blueprint $table) {
            $table->smallInteger('vmwVmState')->unsigned()->change();
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
            $table->string('vmwVmState', 128)->change();
        });
    }
}
