<?php
/**
 * Snmpsim.php
 *
 * Light wrapper around Snmpsim
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App;
use LibreNMS\Config;
use LibreNMS\Proc;

class Snmpsim
{
    private $snmprec_dir;
    private $ip;
    private $port;
    private $log;
    /** @var Proc */
    private $proc;

    public function __construct($ip = '127.1.6.1', $port = 1161, $log = '/tmp/snmpsimd.log')
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->log = $log;
        $this->snmprec_dir = Config::get('install_dir') . '/tests/snmpsim/';
    }

    /**
     * Run snmpsimd and fork it into the background
     * Captures all output to the log
     *
     * @param int $wait Wait for x seconds after starting before returning
     */
    public function fork($wait = 2)
    {
        if ($this->isRunning()) {
            echo "Snmpsim is already running!\n";

            return;
        }

        $cmd = $this->getCmd();

        if (App::runningInConsole()) {
            echo "Starting snmpsim listening on {$this->ip}:{$this->port}... \n";
            d_echo($cmd);
        }

        $this->proc = new Proc($cmd);

        if ($wait) {
            sleep($wait);
        }

        if (App::runningInConsole() && ! $this->proc->isRunning()) {
            // if starting failed, run snmpsim again and output to the console and validate the data
            passthru($this->getCmd(false) . ' --validate-data');

            if (! is_executable($this->findSnmpsimd())) {
                echo "\nCould not find snmpsim, you can install it with 'pip install snmpsim'.  If it is already installed, make sure snmpsimd or snmpsimd.py is in PATH\n";
            } else {
                echo "\nFailed to start Snmpsim. Scroll up for error.\n";
            }
            exit(1);
        }
    }

    /**
     * Stop and start the running snmpsim process
     */
    public function restart()
    {
        $this->stop();
        $this->proc = new Proc($this->getCmd());
    }

    public function stop()
    {
        if (isset($this->proc)) {
            if ($this->proc->isRunning()) {
                $this->proc->terminate();
            }
            unset($this->proc);
        }
    }

    /**
     * Run snmpsimd but keep it in the foreground
     * Outputs to stdout
     */
    public function run()
    {
        echo "Starting snmpsim listening on {$this->ip}:{$this->port}... \n";
        shell_exec($this->getCmd(false));
    }

    public function isRunning()
    {
        if (isset($this->proc)) {
            return $this->proc->isRunning();
        }

        return false;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->snmprec_dir;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Generate the command for snmpsimd
     *
     * @param bool $with_log
     * @return string
     */
    private function getCmd($with_log = true)
    {
        $cmd = $this->findSnmpsimd();

        $cmd .= " --data-dir={$this->snmprec_dir} --agent-udpv4-endpoint={$this->ip}:{$this->port}";

        if (is_null($this->log)) {
            $cmd .= ' --logging-method=null';
        } elseif ($with_log) {
            $cmd .= " --logging-method=file:{$this->log}";
        }

        return $cmd;
    }

    public function __destruct()
    {
        // unset $this->proc to make sure it isn't referenced
        unset($this->proc);
    }

    public function findSnmpsimd()
    {
        $cmd = Config::locateBinary('snmpsimd');
        if (! is_executable($cmd)) {
            $cmd = Config::locateBinary('snmpsimd.py');
        }

        return $cmd;
    }
}
