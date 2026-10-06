<?php

namespace LibreNMS\RRD;

use App\Facades\LibrenmsConfig;
use Closure;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdExecutableNotFoundException;
use LibreNMS\Exceptions\RrdTimeoutException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class RrdProcess
{
    const COMMAND_COMPLETE = 'OK u:';

    private readonly string $rrd_dir;
    private readonly InputStream $input;

    private ?Process $process = null;
    private Closure $processFactory;

    /** @param  int|null  $lifetime  Optional total process lifetime in seconds */
    public function __construct(private readonly LoggerInterface $logger, private readonly int $timeout = 300, ?Closure $processFactory = null, private readonly ?int $lifetime = null)
    {
        $this->rrd_dir = Str::finish(LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd'), '/');
        $this->input = new InputStream();

        // set up process factory
        if ($processFactory === null) {
            $command = [LibrenmsConfig::get('rrdtool', 'rrdtool'), '-'];
            $env = ['LC_ALL' => 'C']; // force english/standard output
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
            $this->process->setTimeout($this->lifetime);
            $this->process->setIdleTimeout($this->timeout);
            $this->process->start();
        }
    }

    /** Symfony's idle clock starts at the last output, so exclude time between commands. */
    private function renewIdleTimeout(): void
    {
        $lastOutput = $this->process->getLastOutputTime();

        if ($lastOutput === null) {
            return;
        }

        $elapsed = max(0, microtime(true) - $lastOutput);

        $this->process->setIdleTimeout($this->timeout + $elapsed);
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

        try {
            $this->waitFor($waitFor);
        } catch (ProcessTimedOutException $e) {
            throw RrdTimeoutException::fromProcessTimeout($e, $command);
        }

        $output = $this->process->getOutput();

        if ($waitFor === self::COMMAND_COMPLETE) {
            $output = substr($output, 0, strrpos($output, $waitFor)); // remove OK line
        }

        return rtrim($output);
    }

    /** @throws ProcessTimedOutException */
    private function waitFor(string $waitFor): void
    {
        $this->process->waitUntil(function ($type, $buffer) use ($waitFor) {
            $this->renewIdleTimeout();

            if ($type === Process::ERR) {
                if (str_contains($buffer, 'rrdtool: not found')) {
                    throw new RrdExecutableNotFoundException(trim($buffer));
                }

                if (str_contains($buffer, 'ERROR: ')) {
                    throw RrdException::parse($buffer);
                }

                if (trim($buffer) !== '') {
                    $this->logger->warning('RRDtool stderr: ' . trim($buffer));
                }

                return false;
            }

            if (str_contains($buffer, 'ERROR: ')) {
                throw RrdException::parse($buffer);
            }

            return str_contains($buffer, $waitFor);
        });
    }

    private function runAsync(string $command): void
    {
        $this->start();

        $this->logger->debug("RRD[%g$command%n]", ['color' => true]);
        $this->process->clearOutput();
        $this->renewIdleTimeout();
        $this->input->write("$command\n");
    }

    public function __destruct()
    {
        $this->stop();
    }
}
