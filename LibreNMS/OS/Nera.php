<?php

/**
 * Nera.php
 *
 * Nera Evolution Series microwave radios (XPAND IP / METRO, enterprise 2378)
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

class Nera extends OS implements WirelessPowerDiscovery, WirelessSnrDiscovery
{
    private const POWER_MEASUREMENTS = [
        'RF INPUT LEVEL' => 'RX Main',
        'RF INPUT LEVEL SPACE' => 'RX Diversity',
        'RF OUTPUT LEVEL' => 'TX',
    ];

    private const SNR_MEASUREMENTS = [
        'SNR' => 'SNR',
    ];

    /** @var array<int|string, array{name: string, entity: string, value: string|null}>|null */
    private ?array $measurements = null;

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
        $sensors = [];
        foreach ($this->measurements() as $index => $measurement) {
            $label = $wanted[$measurement['name']] ?? null;
            if ($label === null) {
                continue;
            }

            $sensors[] = new WirelessSensor(
                $type,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2378.1.1.2.2.2.2.1.1.5.' . $index, // measurement value
                'nera',
                $index,
                trim($this->entityLabel($measurement['entity']) . ' ' . $label),
                is_numeric($measurement['value']) ? (float) $measurement['value'] : null
            );
        }

        return $sensors;
    }

    /**
     * Walk the self-describing measurements table once and cache it. There is no
     * usable MIB for this firmware, so the numeric columns are read directly and
     * rows are selected by their name column. Indexed by the measurement-instance
     * sub-identifier (the last OID element).
     *
     * @return array<int|string, array{name: string, entity: string, value: string|null}>
     */
    private function measurements(): array
    {
        if ($this->measurements === null) {
            $names = SnmpQuery::walk('.1.3.6.1.4.1.2378.1.1.2.2.2.2.1.1.3')->pluck();    // measurement name
            $entities = SnmpQuery::walk('.1.3.6.1.4.1.2378.1.1.2.2.2.2.1.1.2')->pluck(); // entity path, e.g. /ne/frame-1/slot-4/odu
            $values = SnmpQuery::walk('.1.3.6.1.4.1.2378.1.1.2.2.2.2.1.1.5')->pluck();    // value, already scaled (dBm / dB)

            $this->measurements = [];
            foreach ($names as $index => $name) {
                $this->measurements[$index] = [
                    'name' => (string) $name,
                    'entity' => (string) ($entities[$index] ?? ''),
                    'value' => isset($values[$index]) ? (string) $values[$index] : null,
                ];
            }
        }

        return $this->measurements;
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
