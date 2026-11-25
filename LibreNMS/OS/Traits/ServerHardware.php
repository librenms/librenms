<?php

/*
 * ServerHardware.php
 *
 * The server management agents of Dell, HP and Supermicro: hardware, serial and sensors
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Sensor;
use Illuminate\Support\Collection;
use SnmpQuery;

/**
 * For OS that run on servers. The agent of the server vendor tells the hardware and serial and has the sensors.
 */
trait ServerHardware
{
    use DellOpenManage;
    use HpInsightAgents;
    use SupermicroHealthMib;

    protected function discoverServerHardware(): void
    {
        $device = $this->getDevice();

        if ($this->hasDellOpenManage()) {
            $model = $this->dellChassis('chassisModelName');
            $device->hardware = $model ? "Dell $model" : $device->hardware;
            $device->serial = $this->dellChassis('chassisServiceTagName') ?: $device->serial;
        } elseif ($this->hasHpInsightAgents()) {
            $device->hardware = $this->hpServerInfo('cpqSiProductName') ?: $device->hardware;
            $device->serial = $this->hpServerInfo('cpqSiSysSerialNum') ?: $device->serial;
        } else {
            // Supermicro SuperDoctor 5
            $model = $this->supermicroBoard('mbProductName');
            $device->hardware = $model ? "Supermicro $model" : $device->hardware;
            $device->serial = $this->supermicroBoard('mbSerialNumber') ?: $device->serial;
        }
    }

    /**
     * The sensors of the server management agent that answers, there is one per server
     *
     * @return Collection<int, Sensor>
     */
    protected function discoverServerHardwareSensors(): Collection
    {
        if ($this->hasDellOpenManage()) {
            return $this->discoverDellOpenManageSensors();
        }

        if ($this->hasHpInsightAgents()) {
            return $this->discoverHpInsightSensors();
        }

        return $this->discoverSupermicroSensors();
    }

    private function supermicroBoard(string $name): string
    {
        return trim((string) SnmpQuery::cache()->mibDir('supermicro')->get("SUPERMICRO-SD5-MIB::$name.1")->value());
    }
}
