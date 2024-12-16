<?php
/**
 * SafCfml4.php
 *
 * Saf CFM wireless radios
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
 * @copyright  2018 Janno Schouwenburg
 * @author     Janno Schouwenburg <handel@janno.nl>
 */

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class SafCfm extends OS implements
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessErrorsDiscovery
{
    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;
        $response = SnmpQuery::hideMib()->walk('SAF-MPMUX-MIB::mpmux');

        if (! $response->isValid()) {
            return $inventory;
        }

        // all scalar values, so remove the .0
        $data = $response->table(1)[0] ?? [];
        $entIndex = 1;

        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 1,
            'entPhysicalDescr' => $data['termProduct'],
            'entPhysicalVendorType' => $data['termProduct'],
            'entPhysicalContainedIn' => '0',
            'entPhysicalClass' => 'chassis',
            'entPhysicalParentRelPos' => '-1',
            'entPhysicalName' => 'Chassis',
            'entPhysicalSerialNum' => $data['serialNumber'],
            'entPhysicalMfgName' => 'SAF',
            'entPhysicalModelName' => $data['serialNumber'],
            'entPhysicalIsFRU' => 'true',
        ]));

        foreach ([1 => 'rf1Version', 2 => 'rf2Version'] as $index => $item) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 10 + $index,
                'entPhysicalDescr' => $data[$item],
                'entPhysicalVendorType' => 'radio',
                'entPhysicalContainedIn' => 1,
                'entPhysicalClass' => 'module',
                'entPhysicalParentRelPos' => $index,
                'entPhysicalName' => "Radio $index",
                'entPhysicalIsFRU' => 'true',
            ]));
        }

        if ($data['termProduct'] == 'SAF CFM-M4P-MUX') {
            foreach (range(1, 4) as $index) {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => 20 + $index,
                    'entPhysicalDescr' => 'Module Container',
                    'entPhysicalVendorType' => 'containerSlot',
                    'entPhysicalContainedIn' => 1,
                    'entPhysicalClass' => 'container',
                    'entPhysicalParentRelPos' => $index + 2,
                    'entPhysicalName' => "Slot $index",
                    'entPhysicalIsFRU' => 'false',
                ]));
            }

            foreach ([1 => 'm1Description', 2 => 'm2Description', 3 => 'm3Description', 4 => 'm4Description'] as $index => $item) {
                if (! str_contains($data[$item], 'N/A')) {
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => 30 + $index,
                        'entPhysicalDescr' => $data[$item],
                        'entPhysicalVendorType' => 'module',
                        'entPhysicalContainedIn' => $index + 3,
                        'entPhysicalClass' => 'module',
                        'entPhysicalParentRelPos' => 1,
                        'entPhysicalName' => "Module $index",
                        'entPhysicalIsFRU' => 'true',
                    ]));
                }
            }
        }

        return $inventory;
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return [
            // SAF-MPMUX-MIB::cfml4radioTxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.3.6.0',
                'saf-cfml4-tx',
                'cfml4radioR1TxFrequency',
                'Radio 1 Tx Frequency'
            ),
            // SAF-MPMUX-MIB::cfml4radioRxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.3.7.0',
                'saf-cfml4-rx',
                'cfml4radioR1RxFrequency',
                'Radio 1 Rx Frequency'
            ),
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.4.6.0',
                'saf-cfml4-tx',
                'cfml4radioR2TxFrequency',
                'Radio 2 Tx Frequency'
            ),
            // SAF-MPMUX-MIB::cfml4radioRxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.4.7.0',
                'saf-cfml4-rx',
                'cfml4radioR2RxFrequency',
                'Radio 2 Rx Frequency'
            ),
        ];
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        return [
            // SAF-MPMUX-MIB::rf1TxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.3.8.0',
                'saf-cfml4-tx-power',
                'cfml4radioR1TxPower',
                'Radio 1 Tx Power'
            ),
            // SAF-MPMUX-MIB::rf1RxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.3.10.0',
                'saf-cfml4-rx-level',
                'cfml4radioR1RxLevel',
                'Radio 1 Rx Level'
            ),
            // SAF-MPMUX-MIB::rf2TxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.4.8.0',
                'saf-cfml4-tx-power',
                'cfml4radioR2TxPower',
                'Radio 2 Tx Power'
            ),
            // SAF-MPMUX-MIB::rf2RxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.4.10.0',
                'saf-cfml4-rx-level',
                'cfml4radioR2RxLevel',
                'Radio 2 Rx Level'
            ),
        ];
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessErrors()
    {
        return [
            // SAF-MPMUX-MIB::termFrameErrors
            new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.1.10.0',
                'saf-cfml4',
                'cfml4termFrameErrors',
                'Frame errors'
            ),
            // SAF-MPMUX-MIB::termBFrameErr
            new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.1.29.0',
                'saf-cfml4',
                'cfml4termBFrameErr',
                'Background Frame errors'
            ),
        ];
    }
}
