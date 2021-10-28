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
 *
 * @copyright  2021 Trym Lund Flogard
 * @author     Trym Lund Flogard <trym@flogard.no>
 */

namespace LibreNMS\IPMI;

use ErrorException;

/**
 * Represents an IPMI connection with a host machine.
 */
class IPMIClient
{
    /**
     * @var string $ipmiToolPath Absolute path to ipmitool binary.
     */
    private $ipmiToolPath;

    /**
     * @var string $host Hostname or address to connect to.
     */
    private $host;

    /**
     * @var string $user IPMI username.
     */
    private $user;

    /**
     * @var string $password IPMI password.
     */
    private $password;

    /**
     * @var string|null $port IPMI port.
     * @optio
     */
    private $port = null;

    /**
     * @var string $privLvl IPMI privilege level.
     */
    private $privLvl = 'USER';

    /**
     * @var string $interface Connection interface to the BMC.
     * 
     * @see https://linux.die.net/man/1/ipmitool ipmitool man pages.
     */
    private $interface = 'lanplus';

    /**
     * Creates a new instance of the IPMIClient class.
     *
     * @param  string  $ipmiToolPath  The absolute path to ipmitool.
     * @param  string  $host  The hostname or IP of the endpoint. Set to 'localhost' to connect via in-band driver.
     * @param  string  $user
     * @param  string  $password
     */
    public function __construct(string $ipmiToolPath, string $host, string $user, string $password)
    {
        $this->ipmiToolPath = $ipmiToolPath;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Gets the connection hostname.
     */
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
     * 
     * @param string $interface The connection interface passed to ipmitool.
     */
    public function setDriver(string $interface): void
    {
        $this->interface = $interface;
    }

    /**
     * Gets the port used by the client.
     *
     * @return string|null The port currently used. Null if not specified.
     */
    public function getPort(): ?string
    {
        return $this->port;
    }

    /**
     * Set the port used by the client.
     * 
     * @param string|null $port The port used by ipmitool, or null for default.
     */
    public function setPort(?string $port): void
    {
        $this->port = $port;
    }

    /**
     * Gets a binary representation of the SDR record for this host.
     *
     * @return string|null The SDR binary, or null on failure.
     */
    public function getRawSDR()
    {
        try {
            $b64 = base64_encode($this->fetchSDR());

            return base64_decode($b64);
        } catch (\Throwable $th) {
            echo 'Failed to fetch SDR: ' . $th->getMessage() . "\n";

            return null;
        }
    }

    /**
     * Gets a list of sensors and threshold values reported by ipmitool.
     * 
     * @return array A list of available sensors from ipmitool.
     */
    public function getSensors(): array
    {
        return explode(PHP_EOL, $this->send('sensor'));
    }

    /**
     * Gets a comma-separated list of sensor values from the
     * Sensor Data Repository (SDR).
     * 
     * @return array Sensor readings from ipmitool.
     */
    public function getSensorDataRepository(): array
    {
        return explode(PHP_EOL, $this->send('-c sdr'));
    }

    /**
     * Sends an ipmitool command with specified parameters.
     *
     * @param  string  $command  the command to send.
     * @param  bool  $escalatePrivileges  a boolean indicating whether to use 'USER' or 'ADMINISTRATOR' privilege.
     * @return null|string The stdout of the command as reported by ipmitool.
     */
    public function sendCommand(string $command, bool $escalatePrivileges = false): ?string
    {
        return $this->send($command, $escalatePrivileges);
    }

    private function send(string $command, bool $escalate = false): ?string
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
