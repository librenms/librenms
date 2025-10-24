<?php

namespace LibreNMS\RRD;

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\RrdException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class RrdProcess
{
    const COMMAND_COMPLETE = 'OK u:';

    private string $rrdcached;
    private string $rrd_dir;
    private string $rrdtool_exec;
    private array $env = [];
    private InputStream $input;

    private ?Process $process = null;

    public function __construct(private LoggerInterface $logger, private int $timeout = 300)
    {
        $this->rrdtool_exec = LibrenmsConfig::get('rrdtool', 'rrdtool');
        $this->rrdcached = (string) LibrenmsConfig::get('rrdcached', '');
        $this->rrd_dir = LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd');
        $this->input = new InputStream();

        if ($this->rrdcached) {
            $this->env['RRDCACHED_ADDRESS'] = $this->rrdcached;
        }
    }

    public function start(): void
    {
        if ($this->process === null) {
            $this->process = new Process(
                command: [$this->rrdtool_exec, '-'],
                cwd: $this->rrd_dir,
                env: $this->env,
            );
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

        $this->process->clearOutput();
        $this->process->waitUntil(function ($type, $buffer) use ($waitFor) {
            if ($type === Process::ERR) {
                throw new RrdException($buffer);
            }

            if (str_contains($buffer, 'ERROR: ')) {
                preg_match('/ERROR: (.*)/', $buffer, $matches);
                throw new RrdException($matches[1]);
            }

            return str_contains($buffer, $waitFor);
        });

        return rtrim($this->process->getOutput());
    }

    public function runAsync(string $command): void
    {
        $this->start();
        $this->logger->debug("RRD[%g$command%n]", ['color' => true]);
        $this->input->write("$command\n");
    }

    public function __destruct()
    {
        $this->stop();
    }
}
