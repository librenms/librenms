<?php

namespace LibreNMS\Data\Source\Icmp;

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\FpingUnparsableLine;
use Log;
use Symfony\Component\Process\Process;

readonly class FpingAvailabilityService
{
    private int $timeout;
    private int $retries;
    private int $tos;

    public function __construct()
    {
        $this->timeout = max(LibrenmsConfig::get('fping_options.timeout', 500), LibrenmsConfig::get('fping_options.interval', 500));
        $this->retries = LibrenmsConfig::get('fping_options.retries', 2);
        $this->tos = LibrenmsConfig::get('fping_options.tos', 0);
    }

    /**
     * @param  array  $hosts
     * @param  callable  $callback
     * @return void
     */
    public function bulkPing(array $hosts, callable $callback): void
    {
        $cmd = FpingCommandBuilder::make()
            ->fromFile('-')
            ->withTimeout($this->timeout)
            ->withRetries($this->retries)
            ->withTos($this->tos)
            ->build();

        $process = app()->make(Process::class, ['command' => $cmd]);
        $process->setTimeout(LibrenmsConfig::get('rrd.step', 300) * 2);
        $process->setInput(implode(PHP_EOL, $hosts) . PHP_EOL);

        Log::debug('[FPING] ' . $process->getCommandLine() . PHP_EOL);

        $partials = [Process::ERR => '', Process::OUT => ''];

        $process->run(function ($type, $output) use ($callback, &$partials): void {
            $data = $partials[$type] . $output;
            $lines = explode(PHP_EOL, $data);
            $partials[$type] = array_pop($lines);

            foreach ($lines as $line) {
                $line = trim($line);
                if (! $line) {
                    continue;
                }

                try {
                    $response = FpingAliveResponse::parseLine($line);
                    call_user_func($callback, $response);
                } catch (FpingUnparsableLine) {
                    // ignore
                }
            }
        });

        foreach ($partials as $line) {
            $line = trim($line);
            if ($line) {
                try {
                    $response = FpingAliveResponse::parseLine($line);
                    call_user_func($callback, $response);
                } catch (FpingUnparsableLine) {
                    // ignore
                }
            }
        }
    }
}
