<?php
/**
 * Proc.php
 *
 * Executes a process with proc_open() and guarantees it is terminated on exit
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use Exception;
use Illuminate\Support\Str;

class Proc
{
    /**
     * @var resource the process this object is responsible for
     */
    private $_process;
    /**
     * @var array array of process pipes [stdin,stdout,stderr]
     */
    private $_pipes;

    /**
     * @var bool if this process is synchronous (waits for output)
     */
    private $_synchronous;

    /**
     * @var int|null hold the exit code, we can only get this on the first process_status after exit
     */
    private $_exitcode = null;

    /**
     * Create and run a new process
     * Most arguments match proc_open()
     *
     * @param string $cmd the command to execute
     * @param array $descriptorspec the definition of pipes to initialize
     * @param null $cwd working directory to change to
     * @param array|null $env array of environment variables to set
     * @param bool $blocking set the output pipes to blocking (default: false)
     * @throws Exception the command was unable to execute
     */
    public function __construct(
        $cmd,
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $cwd = null,
        $env = null,
        $blocking = false
    ) {
        $this->_process = proc_open($cmd, $descriptorspec, $this->_pipes, $cwd, $env);
        if (! is_resource($this->_process)) {
            throw new Exception("Command failed: $cmd");
        }
        stream_set_blocking($this->_pipes[1], $blocking);
        stream_set_blocking($this->_pipes[2], $blocking);
        $this->_synchronous = true;
    }

    /**
     * Called when this object goes out of scope or php exits
     * If it is still running, terminate the process
     */
    public function __destruct()
    {
        if ($this->isRunning()) {
            $this->terminate();
        }
    }

    /**
     * Get one of the pipes
     * 0 - stdin
     * 1 - stdout
     * 2 - stderr
     *
     * @param int $nr pipe number (0-2)
     * @return resource the pipe handle
     */
    public function pipe($nr)
    {
        return $this->_pipes[$nr];
    }

    /**
     * Send a command to this process and return the output
     * the output may not correspond to this command if this
     * process is not synchronous
     * If the command isn't terminated with a newline, add one
     *
     * @param string $command
     * @return array
     */
    public function sendCommand($command)
    {
        $this->sendInput($this->checkAddEOL($command));

        return $this->getOutput();
    }

    /**
     * Send data to stdin
     *
     * @param string $data the string to send
     */
    public function sendInput($data)
    {
        fwrite($this->_pipes[0], $data);
    }

    /**
     * Gets the current output of the process
     * If this process is set to synchronous, wait for output
     *
     * @param int $timeout time to wait for output, only applies if this process is synchronous
     * @return array [stdout, stderr]
     */
    public function getOutput($timeout = 15)
    {
        if ($this->_synchronous) {
            $pipes = [$this->_pipes[1], $this->_pipes[2]];
            $w = null;
            $x = null;

            stream_select($pipes, $w, $x, $timeout);
        }

        return [stream_get_contents($this->_pipes[1]), stream_get_contents($this->_pipes[2])];
    }

    /**
     * Close all pipes for this process
     */
    private function closePipes()
    {
        foreach ($this->_pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
    }

    /**
     * Attempt to gracefully close this process
     * optionally send one last piece of input
     * such as a quit command
     *
     * ** Warning: this will block until the process closes.
     * Some processes might not close on their own.
     *
     * @param string $command the final command to send (appends newline if one is ommited)
     * @return int the exit status of this process (-1 means error)
     */
    public function close($command = null)
    {
        if (isset($command)) {
            try {
                if (is_resource($this->_pipes[0])) {
                    $this->sendInput($this->checkAddEOL($command));
                }
            } catch (\ErrorException $e) {
                // might have closed already
            }
        }

        $this->closePipes();

        return proc_close($this->_process);
    }

    /**
     * Forcibly close this process
     * Please attempt to run close() instead of this
     * This will be called when this object is destroyed if the process is still running
     *
     * @param int $timeout how many microseconds to wait before terminating (SIGKILL)
     * @param int $signal the signal to send
     * @throws Exception
     */
    public function terminate($timeout = 3000, $signal = 15)
    {
        $status = $this->getStatus();

        $this->closePipes();

        $closed = proc_terminate($this->_process, $signal);

        $time = 0;
        while ($time < $timeout) {
            $closed = ! $this->isRunning();
            if ($closed) {
                break;
            }

            usleep(100);
            $time += 100;
        }

        if (! $closed) {
            // try harder
            if (function_exists('posix_kill')) {
                $killed = posix_kill($status['pid'], 9); //9 is the SIGKILL signal
            } else {
                $killed = proc_terminate($this->_process, 9);
            }
            proc_close($this->_process);

            if (! $killed && $this->isRunning()) {
                throw new Exception('Terminate failed!');
            }
        }
    }

    /**
     * Get the status of this process
     * see proc_get_status()
     *
     * @return array status array
     */
    public function getStatus()
    {
        $status = proc_get_status($this->_process);

        if ($status['running'] === false && is_null($this->_exitcode)) {
            $this->_exitcode = $status['exitcode'];
        }

        return $status;
    }

    /**
     * Check if this process is running
     *
     * @return bool
     */
    public function isRunning()
    {
        if (! is_resource($this->_process)) {
            return false;
        }
        $st = $this->getStatus();

        return isset($st['running']) && $st['running'];
    }

    /**
     * Returns the exit code from the process.
     * Will return null unless isRunning() or getStatus() has been run and returns false.
     *
     * @return int|null
     */
    public function getExitCode()
    {
        return $this->_exitcode;
    }

    /**
     * If this process waits for output
     *
     * @return bool
     */
    public function isSynchronous()
    {
        return $this->_synchronous;
    }

    /**
     * Set this process as synchronous, by default processes are synchronous
     * It is advisable not to change this mid way as output could get mixed up
     * or you could end up blocking until the getOutput timeout expires
     *
     * @param bool $synchronous
     */
    public function setSynchronous($synchronous)
    {
        $this->_synchronous = $synchronous;
    }

    /**
     * Add and end of line character to a string if
     * it doesn't already end with one
     *
     * @param string $string
     * @return string
     */
    private function checkAddEOL($string)
    {
        if (! Str::endsWith($string, PHP_EOL)) {
            $string .= PHP_EOL;
        }

        return $string;
    }
}
