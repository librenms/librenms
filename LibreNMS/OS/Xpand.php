<?php

/**
 * Xpand.php
 *
 * XPAND IP microwave radios (Nera / EVOLUTION, enterprise 2378)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Maikel de Boer
 * @author     Maikel de Boer <mdb@tampnet.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Xpand extends OS implements WirelessPowerDiscovery, WirelessSnrDiscovery
{
    /*
     * The current firmware exposes a self-describing "measurements" table that is
     * not covered by any usable MIB, so discovery walks the numeric columns and
     * selects rows by their name column. The table is indexed by a single
     * measurement-instance sub-identifier (the last OID element).
     *
     *   .2 entity  e.g. /ne/frame-1/slot-4/odu or /ne/frame-1/slot-4/riu
     *   .3 name    the metric selector (RF INPUT LEVEL, SNR, ...)
     *   .5 value   already scaled in the reported unit (dBm / dB), no divisor
     */
    private const MEAS_ENTITY = '.1.3.6.1.4.1.2378.1.1.2.2.2.2.1.1.2';
    private const MEAS_NAME = '.1.3.6.1.4.1.2378.1.1.2.2.2.2.1.1.3';
    private const MEAS_VALUE = '.1.3.6.1.4.1.2378.1.1.2.2.2.2.1.1.5';

    private const POWER_MEASUREMENTS = [
        'RF INPUT LEVEL' => 'RX Main',
        'RF INPUT LEVEL SPACE' => 'RX Diversity',
        'RF OUTPUT LEVEL' => 'TX',
    ];

    private const SNR_MEASUREMENTS = [
        'SNR' => 'SNR',
    ];

    /**
     * @return \LibreNMS\Device\WirelessSensor[]
     */
    public function discoverWirelessPower()
    {
        return $this->discoverMeasurements(WirelessSensorType::Power, self::POWER_MEASUREMENTS);
    }

    /**
     * @return \LibreNMS\Device\WirelessSensor[]
     */
    public function discoverWirelessSnr()
    {
        return $this->discoverMeasurements(WirelessSensorType::Snr, self::SNR_MEASUREMENTS);
    }

    /**
     * Build sensors from the measurements table, one per row whose name column
     * matches an entry in $wanted (name => label).
     *
     * @param  array<string, string>  $wanted
     * @return \LibreNMS\Device\WirelessSensor[]
     */
    private function discoverMeasurements(WirelessSensorType $type, array $wanted): array
    {
        $names = SnmpQuery::walk(self::MEAS_NAME)->pluck();
        if (empty($names)) {
            return [];
        }

        $entities = SnmpQuery::walk(self::MEAS_ENTITY)->pluck();
        $values = SnmpQuery::walk(self::MEAS_VALUE)->pluck();

        $sensors = [];
        foreach ($names as $index => $name) {
            $label = $wanted[$name] ?? null;
            if ($label === null) {
                continue;
            }

            $value = $values[$index] ?? null;
            $current = is_numeric($value) ? (float) $value : null;

            $sensors[] = new WirelessSensor(
                $type,
                $this->getDeviceId(),
                self::MEAS_VALUE . '.' . $index,
                'xpand',
                $index,
                trim($this->entityLabel($entities[$index] ?? '') . ' ' . $label),
                $current
            );
        }

        return $sensors;
    }

    /**
     * Reduce an entity path to a stable, unique label.
     *
     * A node can hold several frames, each with the same slot numbers
     * (e.g. "/ne/frame-1/slot-4/xcvr" and "/ne/frame-2/slot-4/xcvr"), so the
     * frame must be kept or the descriptions collide:
     *   "/ne/frame-1/slot-4/xcvr" -> "frame-1/slot-4"
     */
    private function entityLabel(string $entity): string
    {
        if (preg_match('#frame-\d+/slot-\d+#', $entity, $matches)) {
            return $matches[0];
        }

        if (preg_match('#slot-\d+#', $entity, $matches)) {
            return $matches[0];
        }

        return trim($entity);
    }
}
