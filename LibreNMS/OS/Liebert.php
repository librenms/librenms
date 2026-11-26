<?php

/**
 * Liebert.php
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
 * @copyright  2026 LibreNMS contributors
 * @author     Andrei-Alexandru Bleortu
 */

namespace LibreNMS\OS;

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\DeviceTypeDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Liebert extends OS implements DeviceTypeDiscovery
{
    // The same agent card serves UPSes, power distribution and cooling units, and
    // lgpAgentDeviceId names the registration branch of the device it manages:
    // lgpAcProducts and lgpAcPACCProducts are the environmental families.
    private const array COOLING_PRODUCTS = [
        '.1.3.6.1.4.1.476.1.42.4.3.',
        '.1.3.6.1.4.1.476.1.42.4.12.',
    ];

    private const string FLEXIBLE = 'LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleExtendedTable';

    private ?bool $coolingUnit = null;

    public function discoverDeviceType(): ?string
    {
        return $this->isCoolingUnit() ? 'environment' : null;
    }

    /**
     * IS-UNITY cards on firmware 8.x serve no LIEBERT-GP-ENVIRONMENTAL-MIB, everything they measure
     * is in lgpFlexibleExtendedTable. Only cooling units read it: on UPS and power distribution cards
     * the same rows repeat what UPS-MIB and LIEBERT-GP-POWER-MIB report, so the table is not walked.
     */
    protected function walkSensorYaml(array $yaml): array
    {
        if (! $this->isCoolingUnit()) {
            return parent::walkSensorYaml(array_map(fn (array $section) => [
                'data' => array_filter($section['data'] ?? [], fn (array $item) => ($item['oid'] ?? null) !== self::FLEXIBLE),
            ] + $section, $yaml));
        }

        $walked = parent::walkSensorYaml($yaml);

        // Cards that serve the environmental MIB also repeat its temperature,
        // humidity and capacity readings in the flexible table.
        $next = array_key_first(SnmpQuery::numeric()->next('.1.3.6.1.4.1.476.1.42.3.4')->values()) ?? '';
        $walked['liebert_environmental'] = (int) str_starts_with((string) $next, '.1.3.6.1.4.1.476.1.42.3.4.');

        // Each row carries its own number of decimals, and yaml takes a divisor. The short
        // label names a row, numbered where one reading has instances: the last part of the
        // index is the instance.
        $rows = $walked[self::FLEXIBLE] ?? [];
        $labels = $walked['LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleBasicTable'] ?? [];
        $reading = fn ($index) => substr((string) $index, 0, strrpos((string) $index, '.'));
        $instances = array_count_values(array_map($reading, array_keys($rows)));
        foreach ($rows as $index => $row) {
            $walked['liebert_flexible_divisor'][$index] = 10 ** (int) ($row['LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleEntryDecimalPosition'] ?? 0);

            $descr = $labels[$index]['LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleEntryDataLabel']
                ?? $row['LIEBERT-GP-FLEXIBLE-MIB::lgpFlexibleEntryDataDescription'] ?? $index;
            if ($instances[$reading($index)] > 1) {
                $descr .= ' ' . substr((string) $index, strlen($reading($index)) + 1);
            }
            $walked['liebert_flexible_descr'][$index] = $descr;
        }

        return $walked;
    }

    /**
     * The legacy liebert includes read no yaml data, and walking the whole yaml for them
     * would walk lgpFlexibleExtendedTable on UPS and power distribution cards too.
     */
    protected function legacySensorPreCache(bool $withYaml = false): array
    {
        return parent::legacySensorPreCache();
    }

    private function isCoolingUnit(): bool
    {
        return $this->coolingUnit ??= Str::startsWith(
            SnmpQuery::numeric()->get('LIEBERT-GP-AGENT-MIB::lgpAgentDeviceId.1')->value(),
            self::COOLING_PRODUCTS
        );
    }
}
