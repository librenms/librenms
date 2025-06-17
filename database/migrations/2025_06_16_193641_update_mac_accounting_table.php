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
        Schema::table('mac_accounting', function (Blueprint $table) {
            $table->unsignedBigInteger('device_id')->after('ma_id');
            $table->unsignedInteger('ifIndex')->after('mac');
            $table->unsignedInteger('vlan')->nullable()->after('ifIndex');
            $table->renameColumn('cipMacHCSwitchedBytes_input', 'bytes_in');
            $table->renameColumn('cipMacHCSwitchedBytes_output', 'bytes_out');
            $table->renameColumn('cipMacHCSwitchedPkts_input', 'packets_in');
            $table->renameColumn('cipMacHCSwitchedPkts_output', 'packets_out');
            $table->renameColumn('poll_prev', 'last_polled');
            $table->dropColumn([
                'in_oid',
                'out_oid',
                'cipMacHCSwitchedBytes_input_prev',
                'cipMacHCSwitchedBytes_input_delta',
                'cipMacHCSwitchedBytes_input_rate',
                'cipMacHCSwitchedBytes_output_prev',
                'cipMacHCSwitchedBytes_output_delta',
                'cipMacHCSwitchedBytes_output_rate',
                'cipMacHCSwitchedPkts_input_prev',
                'cipMacHCSwitchedPkts_input_delta',
                'cipMacHCSwitchedPkts_input_rate',
                'cipMacHCSwitchedPkts_output_prev',
                'cipMacHCSwitchedPkts_output_delta',
                'cipMacHCSwitchedPkts_output_rate',
                'poll_period',
                'poll_time',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mac_accounting', function (Blueprint $table) {
            $table->dropColumn(['device_id', 'ifIndex', 'vlan']);
            $table->string('in_oid', 128);
            $table->string('out_oid', 128);
            $table->renameColumn('bytes_in', 'cipMacHCSwitchedBytes_input');
            $table->renameColumn('bytes_out', 'cipMacHCSwitchedBytes_output');
            $table->renameColumn('packets_in', 'cipMacHCSwitchedPkts_input');
            $table->renameColumn('packets_out', 'cipMacHCSwitchedPkts_output');
            $table->bigInteger('cipMacHCSwitchedBytes_input_prev')->nullable();
            $table->bigInteger('cipMacHCSwitchedBytes_input_delta')->nullable();
            $table->integer('cipMacHCSwitchedBytes_input_rate')->nullable();
            $table->bigInteger('cipMacHCSwitchedBytes_output_prev')->nullable();
            $table->bigInteger('cipMacHCSwitchedBytes_output_delta')->nullable();
            $table->integer('cipMacHCSwitchedBytes_output_rate')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_input_prev')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_input_delta')->nullable();
            $table->integer('cipMacHCSwitchedPkts_input_rate')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_output_prev')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_output_delta')->nullable();
            $table->integer('cipMacHCSwitchedPkts_output_rate')->nullable();
            $table->renameColumn('last_polled', 'poll_prev');
            $table->unsignedInteger('poll_period')->nullable();
            $table->unsignedInteger('poll_time')->nullable();
        });
    }
};
