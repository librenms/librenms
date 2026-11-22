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
use LibreNMS\Enum\SensorType;
use LibreNMS\OS;
use SnmpQuery;

class GwEydfa extends OS
{
    protected function discoverOsSensors(): Collection
    {
        $sensors = parent::discoverOsSensors();

        $pumps = SnmpQuery::walk('NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaPumpTable')->table(1);
        foreach ($pumps as $index => $pump) {
            $this->addEydfaSensor($sensors, SensorType::Current, ".1.3.6.1.4.1.17409.1.11.4.1.2.$index", "oaPumpBIAS$index", "BIAS Pump - $index", $pump['NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaPumpBIAS'] ?? null, 1000);

            if ($index == 1) {
                $this->addEydfaSensor($sensors, SensorType::Current, '.1.3.6.1.4.1.17409.1.11.4.1.3.1', 'oaPumpTEC1', 'TEC Pump - 1', $pump['NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaPumpTEC'] ?? null, 100);
                $this->addEydfaSensor($sensors, SensorType::Temperature, '.1.3.6.1.4.1.17409.1.11.4.1.4.1', 'oaPumpTemp1', 'Temperature Pump - 1', $pump['NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaPumpTemp'] ?? null, 10);
            }
        }

        foreach (SnmpQuery::walk('NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaDCPowerVoltage')->pluck() as $index => $value) {
            $this->addEydfaSensor($sensors, SensorType::Voltage, ".1.3.6.1.4.1.17409.1.11.7.1.2.$index", "oaDCPowerVoltage$index", "DC +5V - $index", $value, 10);
        }

        $value = fn (string $oid) => SnmpQuery::get($oid)->value();

        // the internal temperature limits are those of commonInternalTemperature
        $this->addEydfaSensor($sensors, SensorType::Temperature, '.1.3.6.1.4.1.17409.1.3.3.2.2.1.12.1', 'commonDeviceInternalTemperature.1', 'Internal Temp',
            $value('NSCRTV-HFCEMS-COMMON-MIB::commonDeviceInternalTemperature.1'), 1, '.1.3.6.1.4.1.17409.1.3.1.13.0');
        $this->addEydfaSensor($sensors, SensorType::Dbm, '.1.3.6.1.4.1.17409.1.11.2.0', 'oaOutputOpticalPower.0', 'Per Output Power',
            $value('NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaOutputOpticalPower.0'), 10);
        $this->addEydfaSensor($sensors, SensorType::Dbm, '.1.3.6.1.4.1.17409.1.11.3.0', 'oaInputOpticalPower.0', 'Input Power',
            $value('NSCRTV-HFCEMS-OPTICALAMPLIFIER-MIB::oaInputOpticalPower.0'), 10);

        // power supply alarm states (discretePropertyTable)
        $psus = ['.1.3.6.1.4.1.17409.1.1.2.1.4.11.1.3.6.1.4.1.17409.1.11.100.0.2', '.1.3.6.1.4.1.17409.1.1.2.1.4.11.1.3.6.1.4.1.17409.1.11.101.0.2'];
        $states = SnmpQuery::numeric()->get($psus)->values();
        foreach ($psus as $position => $oid) {
            $number = $position + 1;
            $sensors->push(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => $oid,
                'sensor_index' => "PowerSupplyState$number",
                'sensor_type' => 'PowerSupplyState',
                'sensor_descr' => "Power Supply $number",
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $states[$oid] ?? null,
                'rrd_type' => 'GAUGE',
            ])->withStates([
                ['value' => 1, 'generic' => 0, 'descr' => 'normal'],
                ['value' => 7, 'generic' => 1, 'descr' => 'warning'],
                ['value' => 6, 'generic' => 2, 'descr' => 'critical'],
            ]));
        }

        return $sensors;
    }

    /**
     * Limits are in the analogPropertyTable, indexed by the length prefixed oid of the value
     *
     * @param  Collection<int, Sensor>  $sensors
     */
    private function addEydfaSensor(Collection $sensors, SensorType $type, string $oid, string $index, string $descr, mixed $value, int $divisor, ?string $limitOid = null): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $limitOid ??= $oid;
        $limitDivisor = $limitOid === $oid ? $divisor : 1;
        $limitIndex = (count(explode('.', trim($limitOid, '.')))) . $limitOid;
        $limit = function (string $name) use ($limitIndex, $limitDivisor) {
            $limit = SnmpQuery::get("NSCRTV-HFCEMS-PROPERTY-MIB::$name.$limitIndex")->value();

            return is_numeric($limit) ? $limit / $limitDivisor : null;
        };

        $sensors->push(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => $type->value,
            'sensor_oid' => $oid,
            'sensor_index' => $index,
            'sensor_type' => 'gw-eydfa',
            'sensor_descr' => $descr,
            'sensor_divisor' => $divisor,
            'sensor_multiplier' => 1,
            'sensor_current' => $value / $divisor,
            'sensor_limit_low' => $limit('analogAlarmLOLO'),
            'sensor_limit_low_warn' => $limit('analogAlarmLO'),
            'sensor_limit_warn' => $limit('analogAlarmHI'),
            'sensor_limit' => $limit('analogAlarmHIHI'),
            'rrd_type' => 'GAUGE',
        ]));
    }
}
