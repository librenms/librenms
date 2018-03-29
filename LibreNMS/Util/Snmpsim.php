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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Config;
use LibreNMS\Proc;

class Snmpsim
{
    private $snmprec_dir;
    private $ip;
    private $port;
    private $log;
    /** @var Proc $proc */
    private $proc;

    public function __construct($ip = '127.1.6.1', $port = 1161, $log = '/tmp/snmpsimd.log')
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->log = $log;
        $this->snmprec_dir = Config::get('install_dir') . "/tests/snmpsim/";
    }

    public function fork()
    {
        if ($this->isRunning()) {
            echo "Snmpsim is already running!\n";
            return;
        }

        $cmd = $this->getCmd();

        if (isCli()) {
            echo "Starting snmpsim listening on {$this->ip}:{$this->port}... \n";
            d_echo($cmd);
        }

        $this->proc = new Proc($cmd);

        if (isCli() && !$this->proc->isRunning()) {
            echo `tail -5 $this->log` . PHP_EOL;
        }
    }

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

    private function getCmd($with_log = true)
    {
        $cmd = "snmpsimd.py --data-dir={$this->snmprec_dir} --agent-udpv4-endpoint={$this->ip}:{$this->port}";

        if ($with_log) {
            $cmd .= " --logging-method=file:{$this->log}";
        }

        return $cmd;
    }

    public function __destruct()
    {
        // unset $this->proc to make sure it isn't referenced
        unset($this->proc);
    }
}
