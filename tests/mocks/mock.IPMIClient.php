<?php

/**
 * mock.IPMIClient.php
 *
 * IPMI Client Mock
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

class IPMIClientMock extends IPMIClient
{
    private $sdr = '';
    private $sensors = '';
    private $sdrFormatted = '';
    private callable $sendCommandCallback = function ($a, $b) {};

    /**
     * Gets a binary representation of the cached SDR record for this host.
     */
    public function getSDR()
    {
        return $this->sdr;
    }

    public function setSDR($sdr)
    {
        $this->sdr = $sdr;
    }

    /**
     * Gets a list of sensors and threshold values reported by ipmitool.
     */
    public function getSensors()
    {
        return explode(PHP_EOL, $this->sensors);
    }

    public function setSensors($sensors)
    {
        $this->sensors = $sensors;
    }

    /**
     * Gets a comma-separated list of sensor values from the 
     * Sensor Data Repository (SDR).
     */
    public function getSensorDataRepository()
    {
        return explode(PHP_EOL, $this->sdrFormatted);
    }

    public function setSensorDataRepository($sdr)
    {
        $this->sdrFormatted = $sdr;
    }

    /**
     * Sends an ipmitool command with specified parameters.
     * @param string $command the command to send.
     * @param bool $escalatePrivileges a boolean indicating whether to use 'USER' or 'ADMINISTRATOR' privilege.
     */
    public function sendCommand(string $command, bool $escalatePrivileges = false)
    {
        return ($this->sendCommandCallback)($command, $escalatePrivileges);
    }

    public function setSendCommandCallback(callable $func)
    {
        $this->sendCommandCallback = $func;
    }
}
