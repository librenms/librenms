<?php

namespace LibreNMS\RRD;

use App\Facades\LibrenmsConfig;
use Closure;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdExecutableNotFoundException;
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

    /**
     * @param  int  $timeout  seconds to wait for rrdtool to answer a single command
     * @param  int|null  $lifetime  total seconds the process may live, regardless of
     *                              whether rrdtool is answering. Null means unbounded,
     *                              which is what a long-running poll needs.
     */
    public function __construct(private readonly LoggerInterface $logger, private readonly int $timeout = 300, ?Closure $processFactory = null, private readonly ?int $lifetime = null)
    {
        $this->rrdcached = (string) LibrenmsConfig::get('rrdcached', '');
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

    /**
     * Give rrdtool $timeout seconds from now to say something.
     *
     * Called twice per command: once when the command is sent, and again each time
     * rrdtool produces output, so the deadline always sits $timeout seconds ahead
     * of the last thing that actually happened.
     *
     * Symfony compares the idle timeout against the process's *last output*, and
     * that timestamp is not ours to move. rrdtool only speaks when spoken to, so
     * between commands "time since last output" is really "time since we last
     * asked for something" -- which for the poller includes every SNMP walk it
     * does between writes. Left alone, a device that walks for longer than the
     * timeout has its perfectly healthy rrdtool killed, and the failure surfaces
     * at the next write.
     *
     * Since the timestamp cannot be moved forward, the allowance is padded by the
     * time already elapsed against it, which puts the deadline in the same place.
     * At send time that pad is the caller's think-time; once rrdtool is replying
     * the elapsed time is ~0 and the allowance is simply $timeout again, so a
     * command that answers in pieces does not inherit the gap that preceded it.
     *
     * An unresponsive rrdtool is still caught either way: nothing extends the
     * deadline except rrdtool actually speaking.
     */
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
        $this->renewIdleTimeout();
        $this->input->write("$command\n");
    }

    public function __destruct()
    {
        $this->stop();
    }
}
