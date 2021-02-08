<?php
/**
 * Fabos.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Fabos extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $device->version = snmp_get($this->getDeviceArray(), 'swFirmwareVersion.0', '-Ovq', 'SYSTEM-MIB');

        $module = snmp_get($this->getDeviceArray(), 'fcFeModuleObjectID.1', '-Ovqn', 'FIBRE-CHANNEL-FE-MIB');
        $revboard = str_replace('.1.3.6.1.4.1.1588.2.1.1.', '', $module);
        $device->hardware = $this->fcSwitchModelToName($revboard);
    }

    protected function fcSwitchModelToName($model)
    {
        $models = [
            '1' => 'Brocade 1000 Switch',
            '2' => 'Brocade 2800 Switch',
            '3' => 'Brocade 2100/2400 Switch',
            '4' => 'Brocade 20x0 Switch',
            '5' => 'Brocade 22x0 Switch',
            '6' => 'Brocade 2800 Switch',
            '7' => 'Brocade 2000 Switch',
            '9' => 'Brocade 3800 Switch',
            '10' => 'Brocade 12000 Director',
            '12' => 'Brocade 3900 Switch',
            '16' => 'Brocade 3200 Switch',
            '18' => 'Brocade 3000 Switch',
            '21' => 'Brocade 24000 Director',
            '22' => 'Brocade 3016 Switch',
            '26' => 'Brocade 3850 Switch',
            '27' => 'Brocade 3250 Switch',
            '29' => 'Brocade 4012 Embedded Switch',
            '32' => 'Brocade 4100 Switch',
            '33' => 'Brocade 3014 Switch',
            '34' => 'Brocade 200E Switch',
            '37' => 'Brocade 4020 Embedded Switch',
            '38' => 'Brocade 7420 SAN Router',
            '40' => 'Fibre Channel Routing (FCR) Front Domain',
            '41' => 'Fibre Channel Routing (FCR) Xlate Domain',
            '42' => 'Brocade 48000 Director',
            '43' => 'Brocade 4024 Embedded Switch',
            '44' => 'Brocade 4900 Switch',
            '45' => 'Brocade 4016 Embedded Switch',
            '46' => 'Brocade 7500 Switch',
            '51' => 'Brocade 4018 Embedded Switch',
            '55.2' => 'Brocade 7600 Switch',
            '58' => 'Brocade 5000 Switch',
            '61' => 'Brocade 4424 Embedded Switch',
            '62' => 'Brocade DCX Backbone',
            '64' => 'Brocade 5300 Switch',
            '66' => 'Brocade 5100 Switch',
            '67' => 'Brocade Encryption Switch',
            '69' => 'Brocade 5410 Blade',
            '70' => 'Brocade 5410 Embedded Switch',
            '71' => 'Brocade 300 Switch',
            '72' => 'Brocade 5480 Embedded Switch',
            '73' => 'Brocade 5470 Embedded Switch',
            '75' => 'Brocade M5424 Embedded Switch',
            '76' => 'Brocade 8000 Switch',
            '77' => 'Brocade DCX-4S Backbone',
            '83' => 'Brocade 7800 Extension Switch',
            '86' => 'Brocade 5450 Embedded Switch',
            '87' => 'Brocade 5460 Embedded Switch',
            '90' => 'Brocade 8470 Embedded Switch',
            '92' => 'Brocade VA-40FC Switch',
            '95' => 'Brocade VDX 6720-24 Data Center Switch',
            '96' => 'Brocade VDX 6730-32 Data Center Switch',
            '97' => 'Brocade VDX 6720-60 Data Center Switch',
            '98' => 'Brocade VDX 6720-76 Data Center Switch',
            '108' => 'Dell M84280k FCoE Embedded Switch',
            '109' => 'Brocade 6510 Switch',
            '116' => 'Brocade VDX 6710 Data Center Switch',
            '117' => 'Brocade 6547 Embedded Switch',
            '118' => 'Brocade 6505 Switch',
            '120' => 'Brocade DCX 8510-8 Backbone',
            '121' => 'Brocade DCX 8510-4 Backbone',
            '124' => 'Brocade 5430 Switch',
            '125' => 'Brocade 5431 Switch',
            '129' => 'Brocade 6548 Switch',
            '130' => 'Brocade M6505 Switch',
            '133' => 'Brocade 6520 Switch',
            '134' => 'Brocade 5432 Switch',
            '148' => 'Brocade 7840 Switch',
            '162' => 'Brocade G620 Switch',
            '170' => 'Brocade G610 Switch',
        ];

        return $models[$model] ?? 'Unknown Brocade FC Switch';
    }
}
