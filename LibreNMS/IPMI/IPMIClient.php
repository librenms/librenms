<?php

/**
 * IPMIClient.php
 *
 * IPMI Client
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
 * @copyright  2021 Trym Lund Flogard
 * @author     Trym Lund Flogard <trym@flogard.no>
 */

namespace LibreNMS\IPMI;

/**
 * Represents an IPMI connection with a host machine.
 */
class IPMIClient
{
    private $ipmiToolPath;
    private $host;
    private $user;
    private $password;
    private $port = null;

    private $privLvl = 'USER';
    private $interface = 'lanplus';

    /**
     * Creates a new instance of the IPMIClient class.
     * @param string $ipmiToolPath The absolute path to ipmitool.
     * @param string $host The hostname or IP of the endpoint. Set to 'localhost' to connect via in-band driver.
     * @param string $user
     * @param string $password
     */
    public function __construct(string $ipmiToolPath, string $host, string $user, string $password)
    {
        $this->ipmiToolPath = $ipmiToolPath;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Gets the IPMI interface used by the client.
     */
    public function getInterface(): ?string
    {
        return $this->interface;
    }

    public function setInterface(string $interface)
    {
        $this->interface = $interface;
    }

    /**
     * Gets the port used by the client.
     * @return ?string The port currently used. Null if not specified.
     */
    public function getPort(): ?string
    {
        return $this->port;
    }

    /**
     * Set the port used by the client.
     */
    public function setPort(string $port)
    {
        $this->port = $port;
    }

    /**
     * Gets a binary representation of the cached SDR record for this host.
     */
    public function getSDR()
    {
        $path = "/tmp/librenms/ipmi/SDR";
        $this->assertSDR($path);

        $filename = "$path/$this->host";
        $sdrFile = fopen($filename, 'r');
        try {
            return fread($sdrFile, filesize($filename));
        } finally {
            fclose($sdrFile);
        }
    }

    /**
     * Gets a list of sensors and threshold values reported by ipmitool.
     */
    public function getSensors()
    {
        return explode(PHP_EOL, $this->send('sensor'));
    }

    /**
     * Gets a comma-separated list of sensor values.
     */
    public function getSensorDataRepository()
    {
        return explode(PHP_EOL, $this->send('-c sdr'));
    }

    /**
     * Sends an ipmitool command with specified parameters.
     * @param string $command the command to send.
     * @param bool $escalatePrivileges a boolean indicating whether to use 'USER' or 'ADMINISTRATOR' privilege.
     */
    public function sendCommand(string $command, bool $escalatePrivileges = false)
    {
        return $this->send($command, $escalatePrivileges);
    }

    private function send($command, $escalate = false)
    {
        $cmd = [$this->ipmiToolPath];
        if ($this->host != 'localhost') {
            array_push($cmd, '-H', $this->host, '-U', $this->user, '-P', $this->password);
            array_push($cmd, '-L', $escalate ? $this->privLvl : 'ADMINISTRATOR');
            if ($this->port) {
                array_push($cmd, '-p', $this->port);
            }

            array_push($cmd, '-I', $this->interface);
        }

        $cmd = array_merge($cmd, explode(' ', $command));

        return external_exec($cmd);
    }

    private function assertSDR(string $path)
    {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (!file_exists("$path/$this->host")) {
            $this->sendCommand("sdr dump $path/$this->host");
        }
    }
}
