<?php

use App\Facades\LibrenmsConfig;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $types = ['alerting', 'billing', 'discovery', 'poller', 'services'];
        foreach ($types as $type) {
            $enabled = LibrenmsConfig::get("service_{$type}_enabled");
            $schedule = LibrenmsConfig::get("schedule_type.$type", 'legacy');

            if ($schedule === 'legacy' && ($enabled === false || $enabled === 0)
                && ! LibrenmsConfig::persist("schedule_type.$type", 'cron')) {
                throw new RuntimeException("Failed to migrate schedule_type.$type");
            }
        }

        $pingEnabled = LibrenmsConfig::get('service_ping_enabled');
        if (LibrenmsConfig::get('schedule_type.ping', 'legacy') === 'legacy'
            && ($pingEnabled === true || $pingEnabled === 1)
            && ! LibrenmsConfig::persist('schedule_type.ping', 'dispatcher')) {
            throw new RuntimeException('Failed to migrate schedule_type.ping');
        }
    }
};
