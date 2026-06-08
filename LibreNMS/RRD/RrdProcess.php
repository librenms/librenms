<?php

namespace LibreNMS\RRD;

use App\Facades\LibrenmsConfig;
use Closure;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\RrdCachedConnectionException;
use LibreNMS\Exceptions\RrdException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class RrdProcess
{
    const COMMAND_COMPLETE = 'OK u:';

    private readonly string $rrdcached;
    private readonly string $rrd_dir;
    private readonly InputStream $input;

    private ?Process $process = null;
    private Closure $processFactory;

    public function __construct(private readonly LoggerInterface $logger, private readonly int $timeout = 300, ?Closure $processFactory = null)
    {
        $this->rrdcached = (string) LibrenmsConfig::get('rrdcached', '');
        $this->rrd_dir = Str::finish(LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd'), '/');
        $this->input = new InputStream();

        // set up process factory
        if ($processFactory === null) {
            $command = [LibrenmsConfig::get('rrdtool', 'rrdtool'), '-'];
            $env = [];
            if (LibrenmsConfig::get('rrdcached', '')) {
                $env['RRDCACHED_ADDRESS'] = LibrenmsConfig::get('rrdcached', '');
            }
            if (session('preferences.timezone')) {
                $env['TZ'] = session('preferences.timezone');
            }
            $this->processFactory = fn () => new Process(
                command: $command,
                cwd: $this->rrd_dir,
                env: $env,
            );
        } else {
            $this->processFactory = $processFactory;
        }
    }

    public function start(): void
    {
        if ($this->process === null || ! $this->process->isRunning()) {
            $this->process = ($this->processFactory)();
            $this->process->setInput($this->input);
            $this->process->setTimeout($this->timeout);
            $this->process->setIdleTimeout($this->timeout);
            $this->process->start();
        }
    }

    public function stop(): void
    {
        if ($this->process) {
            $this->input->write("quit\n");
            $this->process->stop();
            $this->process = null;
        }
    }

    /**
     * @throws RrdException
     */
    public function run(string $command, string $waitFor = self::COMMAND_COMPLETE): string
    {
        $this->runAsync($command);

        $this->process->waitUntil(function ($type, $buffer) use ($waitFor) {
            if ($type === Process::ERR || str_contains($buffer, 'ERROR: ')) {
                throw RrdException::parse($buffer);
            }

            return str_contains($buffer, $waitFor);
        });

        $output = $this->process->getOutput();

        if ($waitFor === self::COMMAND_COMPLETE) {
            $output = substr($output, 0, strrpos($output, $waitFor)); // remove OK line
        }

        return rtrim($output);
    }

    private function runAsync(string $command): void
    {
        $this->start();

        // clean directory path when using rrdcached
        if ($this->rrdcached) {
            $command = str_replace($this->rrd_dir, '', $command);
        }

        $this->logger->debug("RRD[%g$command%n]", ['color' => true]);
        $this->process->clearOutput();
        $this->input->write("$command\n");
    }

    public function __destruct()
    {
        $this->stop();
    }
}
