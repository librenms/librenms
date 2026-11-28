<?php

/**
 * GwEydfa.php
 *
 * GW EYDFA optical amplifier
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\DiscoversSensors;
use LibreNMS\Enum\SensorType;
use LibreNMS\OS;
use SnmpQuery;

class GwEydfa extends OS
{
    /**
     * Power supply alarm states (discretePropertyTable)
     *
     * @return Collection<int, Sensor>
     */
    #[DiscoversSensors(SensorType::State)]
    protected function discoverPowerSupplySensors(): Collection
    {
        $sensors = new Collection;
        $psus = ['.1.3.6.1.4.1.17409.1.1.2.1.4.11.1.3.6.1.4.1.17409.1.11.100.0.2', '.1.3.6.1.4.1.17409.1.1.2.1.4.11.1.3.6.1.4.1.17409.1.11.101.0.2'];
        $states = SnmpQuery::numeric()->get($psus)->values();

        foreach ($psus as $position => $oid) {
            $number = $position + 1;
            $sensors->push(new Sensor([
                'sensor_class' => 'state',
                'sensor_oid' => $oid,
                'sensor_index' => "PowerSupplyState$number",
                'sensor_type' => 'PowerSupplyState',
                'sensor_descr' => "Power Supply $number",
                'sensor_current' => $states[$oid] ?? null,
            ])->withStates([
                ['value' => 1, 'generic' => 0, 'descr' => 'normal'],
                ['value' => 7, 'generic' => 1, 'descr' => 'warning'],
                ['value' => 6, 'generic' => 2, 'descr' => 'critical'],
            ]));
        }

        return $sensors;
    }

    /**
     * The analog readings, their limits are in the analogPropertyTable
     *
     * @return Collection<int, Sensor>
     */
    #[DiscoversSensors(SensorType::Current, SensorType::Dbm, SensorType::Temperature, SensorType::Voltage)]
    protected function discoverAmplifierSensors(): Collection
    {
        $sensors = new Collection;

        // class, oid, index, descr, value, divisor, oid of the limits when they are not those of the value
        $readings = [];

        foreach (SnmpQuery::walk('NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaPumpTable')->table(1) as $index => $pump) {
            $readings[] = [SensorType::Current, ".1.3.6.1.4.1.17409.1.11.4.1.2.$index", "oaPumpBIAS$index", "BIAS Pump - $index", $pump['NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaPumpBIAS'] ?? null, 1000, null];
            if ($index == 1) {
                $readings[] = [SensorType::Current, '.1.3.6.1.4.1.17409.1.11.4.1.3.1', 'oaPumpTEC1', 'TEC Pump - 1', $pump['NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaPumpTEC'] ?? null, 100, null];
                $readings[] = [SensorType::Temperature, '.1.3.6.1.4.1.17409.1.11.4.1.4.1', 'oaPumpTemp1', 'Temperature Pump - 1', $pump['NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaPumpTemp'] ?? null, 10, null];
            }
        }

        // the internal temperature limits are those of commonInternalTemperature
        $readings[] = [SensorType::Temperature, '.1.3.6.1.4.1.17409.1.3.3.2.2.1.12.1', 'commonDeviceInternalTemperature.1', 'Internal Temp',
            SnmpQuery::get('NSCRTV-HFCEMS-COMMON-MIB::commonDeviceInternalTemperature.1')->value(), 1, '.1.3.6.1.4.1.17409.1.3.1.13.0'];

        foreach (SnmpQuery::walk('NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaDCPowerVoltage')->pluck() as $index => $voltage) {
            $readings[] = [SensorType::Voltage, ".1.3.6.1.4.1.17409.1.11.7.1.2.$index", "oaDCPowerVoltage$index", "DC +5V - $index", $voltage, 10, null];
        }

        $readings[] = [SensorType::Dbm, '.1.3.6.1.4.1.17409.1.11.2.0', 'oaOutputOpticalPower.0', 'Per Output Power',
            SnmpQuery::get('NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaOutputOpticalPower.0')->value(), 10, null];
        $readings[] = [SensorType::Dbm, '.1.3.6.1.4.1.17409.1.11.3.0', 'oaInputOpticalPower.0', 'Input Power',
            SnmpQuery::get('NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaInputOpticalPower.0')->value(), 10, null];

        $readings = array_filter($readings, fn (array $reading) => is_numeric($reading[4]));
        if (empty($readings)) {
            return $sensors;
        }

        // the limits are indexed by the length prefixed oid of the value
        $limits = SnmpQuery::numericIndex()->walk([
            'NSCRTV-HFCEMS-PROPERTY-MIB::analogAlarmLOLO',
            'NSCRTV-HFCEMS-PROPERTY-MIB::analogAlarmLO',
            'NSCRTV-HFCEMS-PROPERTY-MIB::analogAlarmHI',
            'NSCRTV-HFCEMS-PROPERTY-MIB::analogAlarmHIHI',
        ])->valuesByIndex();

        foreach ($readings as [$type, $oid, $index, $descr, $value, $divisor, $limitOid]) {
            $limitOid ??= $oid;
            $limitDivisor = $limitOid === $oid ? $divisor : 1;
            $entry = $limits[count(explode('.', trim($limitOid, '.'))) . $limitOid] ?? [];
            [$lowLimit, $lowWarnLimit, $warnLimit, $highLimit] = array_map(
                fn (string $name) => is_numeric($limit = $entry["NSCRTV-HFCEMS-PROPERTY-MIB::$name"] ?? null) ? $limit / $limitDivisor : null,
                ['analogAlarmLOLO', 'analogAlarmLO', 'analogAlarmHI', 'analogAlarmHIHI'],
            );

            $sensors->push(new Sensor([
                'sensor_class' => $type->value,
                'sensor_oid' => $oid,
                'sensor_index' => $index,
                'sensor_type' => 'gw-eydfa',
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_current' => $value / $divisor,
                'sensor_limit_low' => $lowLimit,
                'sensor_limit_low_warn' => $lowWarnLimit,
                'sensor_limit_warn' => $warnLimit,
                'sensor_limit' => $highLimit,
            ]));
        }

        return $sensors;
    }
}
