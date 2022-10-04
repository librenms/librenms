<?php
/**
 * Sensor.php
 *
 * List of sensors and their unit
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
 * @link https://www.librenms.org
 */

namespace LibreNMS\Enum;

abstract class Sensor
{
    const AIRFLOW = 'cfm';
    const BER = 'ratio';
    const BITRATE = 'bps';
    const CHARGE = '%';
    const CHROMATIC_DISPERSION = 'ps/nm';
    const COOLING = 'W';
    const COUNT = '#';
    const CURRENT = 'A';
    const DBM = 'dBm';
    const DELAY = 's';
    const EER = 'eer';
    const FANSPEED = 'rpm';
    const FREQUENCY = 'Hz';
    const HUMIDITY = '%';
    const LOAD = '%';
    const LOSS = '%';
    const PERCENT = '%';
    const POWER = 'W';
    const POWER_CONSUMED = 'kWh';
    const POWER_FACTOR = 'ratio';
    const PRESSURE = 'kPa';
    const QUALITY_FACTOR = 'dB';
    const RUNTIME = 'Min';
    const SIGNAL = 'dBm';
    const SNR = 'SNR';
    const STATE = '#';
    const TEMPERATURE = 'C';
    const TV_SIGNAL = 'dBmV';
    const VOLTAGE = 'V';
    const WATERFLOW = 'l/m';

    const CLASSES = [
        'airflow' => self::AIRFLOW,
        'ber' => self::BER,
        'bitrate' => self::BITRATE,
        'charge' => self::CHARGE,
        'chromatic_dispersion' => self::CHROMATIC_DISPERSION,
        'cooling' => self::COOLING,
        'count' => self::COUNT,
        'current' => self::CURRENT,
        'dBm' => self::DBM,
        'delay' => self::DELAY,
        'eer' => self::EER,
        'fanspeed' => self::FANSPEED,
        'frequency' => self::FREQUENCY,
        'humidity' => self::HUMIDITY,
        'load' => self::LOAD,
        'loss' => self::LOSS,
        'percent' => self::PERCENT,
        'power' => self::POWER,
        'power_consumed' => self::POWER_CONSUMED,
        'power_factor' => self::POWER_FACTOR,
        'pressure' => self::PRESSURE,
        'quality_factor' => self::QUALITY_FACTOR,
        'runtime' => self::RUNTIME,
        'signal' => self::SIGNAL,
        'snr' => self::SNR,
        'state' => self::STATE,
        'temperature' => self::TEMPERATURE,
        'tv_signal' => self::TV_SIGNAL,
        'voltage' => self::VOLTAGE,
        'waterflow' => self::WATERFLOW,
    ];

    public static function fromName(string $name): string
    {
        $name = strtoupper($name);

        return constant("self::$name");
    }
}
