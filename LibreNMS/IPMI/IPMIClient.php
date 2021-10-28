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

use ErrorException;
use Illuminate\Support\Facades\Cache;

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
     * Gets the IPMI interface driver used by the client.
     */
    public function getDriver(): string
    {
        return $this->interface;
    }

    /**
     * Sets the IPMI interface driver.
     */
    public function setDriver(string $interface)
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
    public function setPort(?string $port)
    {
        $this->port = $port;
    }

    /**
     * Gets a binary representation of the SDR record for this host.
     * @return string|false The SDR binary or false on failure.
     */
    public function getRawSDR()
    {
        try {
            $b64 = base64_encode($this->fetchSDR());

            return base64_decode($b64);
        } catch (\Throwable $th) {
            echo 'Failed to fetch SDR: ' . $th->getMessage() . "\n";
        }
    }

    /**
     * Gets a list of sensors and threshold values reported by ipmitool.
     */
    public function getSensors(): array
    {
        return explode(PHP_EOL, $this->send('sensor'));
    }

    /**
     * Gets a comma-separated list of sensor values from the
     * Sensor Data Repository (SDR).
     */
    public function getSensorDataRepository(): array
    {
        return explode(PHP_EOL, $this->send('-c sdr'));
    }

    /**
     * Sends an ipmitool command with specified parameters.
     * @param string $command the command to send.
     * @param bool $escalatePrivileges a boolean indicating whether to use 'USER' or 'ADMINISTRATOR' privilege.
     * @return null|string The stdout of the command as reported by ipmitool.
     */
    public function sendCommand(string $command, bool $escalatePrivileges = false): ?string
    {
        return $this->send($command, $escalatePrivileges);
    }

    private function send(string $command, bool $escalate = false)
    {
        $cmd = [$this->ipmiToolPath];
        if ($this->host != 'localhost') {
            array_push($cmd, '-H', $this->host, '-U', $this->user, '-P', $this->password);
            array_push($cmd, '-L', $escalate ? 'ADMINISTRATOR' : $this->privLvl);
            if ($this->port) {
                array_push($cmd, '-p', $this->port);
            }

            array_push($cmd, '-I', $this->interface);
        }

        $cmd = array_merge($cmd, explode(' ', $command));
        $proc = new IPMICommand($cmd);

        return $proc->execute();
    }

    private function fetchSDR(): string
    {
        $basePath = sys_get_temp_dir() . '/ipmitool';
        if (! is_dir($basePath)) {
            mkdir($basePath, 0777, true);
        }

        $filePath = "$basePath/" . $this->host . '.sdr.tmp';
        unlink($filePath);
        if (! $this->sendCommand("sdr dump $filePath")) {
            throw new ErrorException('The SDR dump command failed');
        }

        try {
            return file_get_contents($filePath);
        } finally {
            unlink($filePath);
        }
    }
}
