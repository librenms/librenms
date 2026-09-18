<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace bill_ports + bill_port_counters with one polymorphic table so a
     * bill can account any billable source (ports, SAPs, ...).
     */
    public function up(): void
    {
        Schema::create('bill_counters', function (Blueprint $table) {
            $table->unsignedInteger('bill_id');
            $table->string('source_type', 32);
            $table->unsignedInteger('source_id');
            $table->tinyInteger('autoadded')->default(0);
            $table->timestamp('timestamp')->useCurrent();
            $table->bigInteger('in_counter')->nullable();
            $table->bigInteger('in_delta')->default(0);
            $table->bigInteger('out_counter')->nullable();
            $table->bigInteger('out_delta')->default(0);
            $table->primary(['bill_id', 'source_type', 'source_id']);
            $table->index(['source_type', 'source_id']);
        });

        $ports = DB::table('bill_ports')
            ->leftJoin('bill_port_counters', function ($join) {
                $join->on('bill_port_counters.bill_id', '=', 'bill_ports.bill_id')
                    ->on('bill_port_counters.port_id', '=', 'bill_ports.port_id');
            })
            ->select([
                'bill_ports.bill_id',
                DB::raw("'interface' as source_type"),
                'bill_ports.port_id',
                'bill_ports.bill_port_autoadded',
                DB::raw('COALESCE(bill_port_counters.timestamp, CURRENT_TIMESTAMP) as timestamp'),
                'bill_port_counters.in_counter',
                DB::raw('COALESCE(bill_port_counters.in_delta, 0) as in_delta'),
                'bill_port_counters.out_counter',
                DB::raw('COALESCE(bill_port_counters.out_delta, 0) as out_delta'),
            ]);

        // legacy bill_ports had no unique key, so duplicate port rows can exist; keep the first
        DB::table('bill_counters')->insertOrIgnoreUsing(
            ['bill_id', 'source_type', 'source_id', 'autoadded', 'timestamp', 'in_counter', 'in_delta', 'out_counter', 'out_delta'],
            $ports
        );

        Schema::drop('bill_port_counters');
        Schema::drop('bill_ports');
    }

    public function down(): void
    {
        Schema::create('bill_ports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bill_id');
            $table->unsignedInteger('port_id');
            $table->tinyInteger('bill_port_autoadded')->default(0);
        });

        Schema::create('bill_port_counters', function (Blueprint $table) {
            $table->unsignedInteger('port_id');
            $table->timestamp('timestamp')->useCurrent();
            $table->bigInteger('in_counter')->nullable();
            $table->bigInteger('in_delta')->default(0);
            $table->bigInteger('out_counter')->nullable();
            $table->bigInteger('out_delta')->default(0);
            $table->unsignedInteger('bill_id');
            $table->primary(['port_id', 'bill_id']);
        });

        $counters = DB::table('bill_counters')->where('source_type', 'interface');

        DB::table('bill_ports')->insertUsing(
            ['bill_id', 'port_id', 'bill_port_autoadded'],
            (clone $counters)->select(['bill_id', 'source_id', 'autoadded'])
        );
        DB::table('bill_port_counters')->insertOrIgnoreUsing(
            ['port_id', 'timestamp', 'in_counter', 'in_delta', 'out_counter', 'out_delta', 'bill_id'],
            (clone $counters)->whereNotNull('in_counter')->select(['source_id', 'timestamp', 'in_counter', 'in_delta', 'out_counter', 'out_delta', 'bill_id'])
        );

        Schema::drop('bill_counters');
    }
};
