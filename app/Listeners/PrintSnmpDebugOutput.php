<?php

namespace App\Listeners;

use App\Events\SnmpQueryExecuted;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Debug;

class PrintSnmpDebugOutput
{
    /** @var array<int, string> */
    private array $commandCleanupPatterns = [
        '/-c\' \'[\S]+\'/',
        '/-u\' \'[\S]+\'/',
        '/-U\' \'[\S]+\'/',
        '/-A\' \'[\S]+\'/',
        '/-X\' \'[\S]+\'/',
        '/-P\' \'[\S]+\'/',
        '/-H\' \'[\S]+\'/',
        '/(udp|udp6|tcp|tcp6):([^:]+):([\d]+)/',
    ];

    /** @var array<int, string> */
    private array $commandReplacementPatterns = [
        '-c\' \'COMMUNITY\'',
        '-u\' \'USER\'',
        '-U\' \'USER\'',
        '-A\' \'PASSWORD\'',
        '-X\' \'PASSWORD\'',
        '-P\' \'PASSWORD\'',
        '-H\' \'HOSTNAME\'',
        '\1:HOSTNAME:\3',
    ];

    private string $output_regex = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
    private string $output_replacement = '*';

    public function handle(SnmpQueryExecuted $event): void
    {
        if (! Debug::isEnabled()) {
            return;
        }

        $commandStr = implode(' ', array_map(escapeshellarg(...), $event->cliCommand));

        if (! Debug::isVerbose()) {
            $debugCommand = preg_replace($this->commandCleanupPatterns, $this->commandReplacementPatterns, $commandStr);
            Log::debug('SNMP[%c' . $debugCommand . '%n]', ['color' => true]);
            Log::debug(preg_replace($this->output_regex, $this->output_replacement, $event->response->raw));
        } else {
            Log::debug('SNMP[%c' . $commandStr . '%n]', ['color' => true]);
            Log::debug($event->response->raw);
        }

        if (! empty($event->response->stderr)) {
            Log::debug($event->response->stderr);
        }
    }
}
