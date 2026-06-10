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
        Schema::table('alert_log', function (Blueprint $table) {
            $table->index(['device_id', 'rule_id', 'time_logged']);
            $table->dropIndex('alert_log_device_id_index');
            $table->dropIndex('alert_log_rule_id_index');
            $table->dropIndex('alert_log_rule_id_device_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alert_log', function (Blueprint $table) {
            $table->index(['rule_id', 'device_id']);
            $table->index(['rule_id']);
            $table->index(['device_id']);
            $table->dropIndex('alert_log_device_id_rule_id_time_logged_index');
        });
    }
};
