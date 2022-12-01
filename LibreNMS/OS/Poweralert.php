<?php
/**
 * Poweralert.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Polling\OSPolling;

class Poweralert extends \LibreNMS\OS implements OSPolling
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $this->customSysName($device);
    }

    public function pollOs(): void
    {
        $this->customSysName($this->getDevice());
    }

    public function getUpsMibDivisor(string $oid): int
    {
        if (in_array($oid, [
            'UPS-MIB::upsBatteryCurrent',
            'UPS-MIB::upsOutputCurrent',
            'UPS-MIB::upsInputCurrent',
            'UPS-MIB::upsBypassCurrent',
            'UPS-MIB::upsInputFrequency',
            'UPS-MIB::upsOutputFrequency',
            'UPS-MIB::upsBypassFrequency',
        ])) {
            if (version_compare($this->getVersion(), '12.06.0068', '>=')) {
                return 10;
            } elseif (version_compare($this->getVersion(), '12.04.0055', '=')) {
                return 10;
            } elseif (version_compare($this->getVersion(), '12.04.0056', '>=')) {
                return 1;
            }
        } elseif ($oid == 'UPS-MIB::upsOutputPercentLoad') {
            if (version_compare($this->getVersion(), '12.06.0064', '=')) {
                return 10;
            } else {
                return 1;
            }
        }

        return parent::getUpsMibDivisor($oid); // fallback
    }

    /**
     * @param  \App\Models\Device  $device
     */
    private function customSysName(Device $device): void
    {
        $device->sysName = \SnmpQuery::get('.1.3.6.1.2.1.33.1.1.5.0')->value() ?: $device->sysName;
    }

    private function getVersion()
    {
        if (! isset($this->cache['poweralert_version'])) {
            $this->cache['poweralert_version'] = \SnmpQuery::get('TRIPPLITE-MIB::tlUpsSnmpCardSerialNum.0')->value();
        }

        return $this->cache['poweralert_version'];
    }
}
