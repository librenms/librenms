<?php

namespace App\Listeners;

use App\Events\SnmpQueryExecuted;
use App\Models\Eventlog;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Debug;

class CheckSnmpExitCode
{
    public function handle(SnmpQueryExecuted $event): void
    {
        if (! $event->response->exitCode) {
            return;
        }

        if (str_starts_with($event->response->stderr, 'Invalid authentication protocol specified')) {
            Eventlog::log('Unsupported SNMP authentication algorithm - ' . $event->response->exitCode, $event->device, 'poller', Severity::Error);
        } elseif (str_starts_with($event->response->stderr, 'Invalid privacy protocol specified')) {
            Eventlog::log('Unsupported SNMP privacy algorithm - ' . $event->response->exitCode, $event->device, 'poller', Severity::Error);
        }

        if (Debug::isEnabled()) {
            Log::debug('Exitcode: ' . $event->response->exitCode, [$event->response->stderr]);
        }
    }
}
