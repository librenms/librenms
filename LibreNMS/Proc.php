<?php
/**
 * Proc.php
 *
 * -Description-
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
    private $_process;
    private $_pipes;

    public function __construct($cmd, $descriptorspec, $cwd = null, $env = null, $blocking = false)
    {
        $this->_process = proc_open($cmd, $descriptorspec, $this->_pipes, $cwd, $env);
        if (!is_resource($this->_process)) {
            throw new Exception("Command failed: $cmd");
        }
        stream_set_blocking($this->_pipes[1], $blocking);
        stream_set_blocking($this->_pipes[2], $blocking);
    }

    public function __destruct()
    {
        if ($this->isRunning()) {
            $this->terminate();
        }
    }

    public function pipe($nr)
    {
        return $this->_pipes[$nr];
    }

    public function sendInput($data)
    {
        d_echo("Sent process input: $data\n");
        if (!ends_with($data, PHP_EOL)) {
            $data .= PHP_EOL;
        }

        fwrite($this->_pipes[0], $data);
    }

    public function getOutput()
    {
        d_echo('Getting process output.' . PHP_EOL);
        return array(stream_get_contents($this->_pipes[1]), stream_get_contents($this->_pipes[2]));
    }

    public function waitForOutput($timeout = 10)
    {
        d_echo('Waiting for process output.' . PHP_EOL);
        $pipes = array($this->_pipes[1], $this->_pipes[2]);
        $w = null;
        $x = null;

        stream_select($pipes, $w, $x, $timeout);
        return $this->getOutput();
    }

    public function terminate($signal = 15)
    {
        $status = $this->getStatus();

        fclose($this->_pipes[1]);
        fclose($this->_pipes[2]);

        $ret = proc_terminate($this->_process, $signal);

        if (!$ret) {
            // try harder
            $pid = $status['pid'];
            $ret = posix_kill($pid, 9); //9 is the SIGKILL signal
            proc_close($this->_process);

            if (!$ret) {
                throw new Exception("Terminate failed!");
            }
        }
    }

    public function close($cmd = null)
    {
        if (isset($cmd)) {
            fwrite($this->_pipes[0], $cmd . PHP_EOL);
        }

        fclose($this->_pipes[0]);
        fclose($this->_pipes[1]);
        fclose($this->_pipes[2]);

        return proc_close($this->_process);
    }

    public function getStatus()
    {
        return proc_get_status($this->_process);
    }

    public function isRunning()
    {
        $st = $this->getStatus();
        return $st['running'];
    }
}
