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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use Exception;

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
    public function __construct($cmd, $descriptorspec, $cwd = null, $env = null, $blocking = false)
    {
        $this->_process = proc_open($cmd, $descriptorspec, $this->_pipes, $cwd, $env);
        if (!is_resource($this->_process)) {
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
     * @param $command
     * @return array
     */
    public function sendCommand($command)
    {
        if (!ends_with($command, PHP_EOL)) {
            $command .= PHP_EOL;
        }
        $this->sendInput($command);

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
            $pipes = array($this->_pipes[1], $this->_pipes[2]);
            $w = null;
            $x = null;

            stream_select($pipes, $w, $x, $timeout);
        }
        return array(stream_get_contents($this->_pipes[1]), stream_get_contents($this->_pipes[2]));
    }

    /**
     * Attempt to gracefully close this process
     * optionally send one last piece of input
     * such as a quit command
     *
     * @param string $cmd the final command to send
     * @return int the exit status of this process (-1 means error)
     */
    public function close($cmd = null)
    {
        if (isset($cmd)) {
            $this->sendInput($cmd);
        }

        fclose($this->_pipes[0]);
        fclose($this->_pipes[1]);
        fclose($this->_pipes[2]);

        return proc_close($this->_process);
    }

    /**
     * Forcibly close this process
     * Please attempt to run close() instead of this
     * This will be called when this object is destroyed if the process is still running
     *
     * @param int $signal the signal to send
     * @throws Exception
     */
    public function terminate($signal = 15)
    {
        $status = $this->getStatus();

        fclose($this->_pipes[1]);
        fclose($this->_pipes[2]);

        $closed = proc_terminate($this->_process, $signal);

        if (!$closed) {
            // try harder
            $pid = $status['pid'];
            $killed = posix_kill($pid, 9); //9 is the SIGKILL signal
            proc_close($this->_process);

            if (!$killed) {
                throw new Exception("Terminate failed!");
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
        return proc_get_status($this->_process);
    }

    /**
     * Check if this process is running
     *
     * @return bool
     */
    public function isRunning()
    {
        if(!is_resource($this->_process)) {
            return false;
        }
        $st = $this->getStatus();
        return isset($st['running']);
    }

    /**
     * If this process waits for output
     * @return boolean
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
     * @param boolean $synchronous
     */
    public function setSynchronous($synchronous)
    {
        $this->_synchronous = $synchronous;
    }
}
