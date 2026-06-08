<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('bill_port_data', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('bill_id');
            $table->unsignedInteger('port_id');
            $table->unsignedInteger('device_id');
            $table->timestamp('timestamp')->useCurrent();
            $table->unsignedInteger('poll_period');
            $table->unsignedBigInteger('in_counter')->default(0);
            $table->unsignedBigInteger('out_counter')->default(0);
            $table->unsignedBigInteger('in_delta')->default(0);
            $table->unsignedBigInteger('out_delta')->default(0);
            $table->boolean('processed')->default(false);

            $table->index(['bill_id', 'timestamp'], 'bill_port_data_bill_id_timestamp_index');
            $table->index(['port_id', 'timestamp'], 'bill_port_data_port_id_timestamp_index');
            $table->index(['processed'], 'bill_port_data_processed_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_port_data');
    }
};
