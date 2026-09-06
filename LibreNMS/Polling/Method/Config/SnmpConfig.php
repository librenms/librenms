<?php
/**
 * SnmpConfig.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling\Method\Config;

use App\Facades\LibrenmsConfig;
use App\Models\Device;

final readonly class SnmpConfig
{
    public function __construct(
        // Secrets
        public string $version = 'v2c',
        public ?string $community = null,
        public ?string $authname = null,
        public ?string $authpass = null,
        public ?string $authlevel = null,
        public ?string $authalgo = null,
        public ?string $cryptopass = null,
        public ?string $cryptoalgo = null,
        public ?string $context = null,

        // Settings
        public string $transport = 'udp',
        public int $port = 161,
        public int|float $timeout = 1,
        public int $retries = 5,
        public int $maxRepeaters = 0,
        public int $maxOid = 10,
    ) {
    }

    public static function fromDevice(Device $device): self
    {
        $rawTimeout = (is_numeric($device->timeout) && $device->timeout > 0)
            ? $device->timeout
            : LibrenmsConfig::get('snmp.timeout', 1);

        $timeout = is_numeric($rawTimeout) && (float) $rawTimeout > 0
            ? ((float) $rawTimeout == (int) $rawTimeout ? (int) $rawTimeout : (float) $rawTimeout)
            : 1;

        $rawRetries = (is_numeric($device->retries) && $device->retries >= 0)
            ? $device->retries
            : LibrenmsConfig::get('snmp.retries', 5);

        $retries = is_numeric($rawRetries) && (int) $rawRetries >= 0
            ? (int) $rawRetries
            : 5;

        $maxRepeaters = (int) ($device->getAttrib('snmp_max_repeaters') ?: LibrenmsConfig::getOsSetting($device->os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', 0)));
        $configuredMaxOid = $device->getAttrib('snmp_max_oid') ?: LibrenmsConfig::getOsSetting($device->os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10));

        return new self(
            version: $device->snmpver ?? 'v2c',
            community: $device->community,
            authname: $device->authname,
            authpass: $device->authpass,
            authlevel: $device->authlevel,
            authalgo: $device->authalgo,
            cryptopass: $device->cryptopass,
            cryptoalgo: $device->cryptoalgo,
            context: $device->context ?? null,
            transport: $device->transport ?? 'udp',
            port: (int) ($device->port ?? 161),
            timeout: $timeout,
            retries: max(0, $retries),
            maxRepeaters: max(0, $maxRepeaters),
            maxOid: max(1, (int) $configuredMaxOid),
        );
    }
}
