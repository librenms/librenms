<?php

/**
 * SnmpConfig.php
 *
 * Value object holding SNMP connection settings and credentials for a target.
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

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Interfaces\PollingMethodConfigInterface;
use SnmpQuery;
use App\Facades\LibrenmsConfig;
use LibreNMS\Util\Rewrite;

readonly class SnmpConfig implements PollingMethodConfigInterface
{
    public function __construct(
        public bool $enabled = true,
        public bool $affectsAvailability = true,

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
        public bool $bulk = true,
    ) {
    }

    public static function fromDevice(Device $device): self
    {
        $method = $device->pollingMethod(PollingMethodType::Snmp);

        if ($method === null) {
            throw new SnmpException('Invalid polling method type');
        }

        $secret = $method->secret;
        $secretData = $secret ? $secret->data : [];

        $timeout = (float) ($method->settings['timeout'] > 0 ? $method->settings['timeout'] : LibrenmsConfig::get('snmp.timeout', 1));
        $retries = (int) (is_numeric($method->settings['retries']) ? $method->settings['retries'] : LibrenmsConfig::get('snmp.retries', 5));
        $maxRepeaters = (int) ($method->settings['max_repeaters'] ?: LibrenmsConfig::getOsSetting($device->os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', 0)));
        $configuredMaxOid = (int) ($method->settings['max_oid'] ?: LibrenmsConfig::getOsSetting($device->os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10)));
        $rawBulk = $device->getAttrib('snmp_bulk') ?? LibrenmsConfig::getOsSetting($device->os, 'snmp_bulk', LibrenmsConfig::get('snmp_bulk', true));

        return new static(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
            version: $secretData['version'] ?? 'v2c',
            community: $secretData['community'] ?? null,
            authname: $secretData['authname'] ?? null,
            authpass: $secretData['authpass'] ?? null,
            authlevel: $secretData['authlevel'] ?? 'noAuthNoPriv',
            authalgo: $secretData['authalgo'] ?? 'SHA',
            cryptopass: $secretData['cryptopass'] ?? null,
            cryptoalgo: $secretData['cryptoalgo'] ?? 'AES',
            context: $secretData['context'] ?? null,
            transport: $method->settings['transport'],
            port: (int) ($method->settings['port'] ?? 161),
            timeout: max(0.1, $timeout),
            retries: max(0, $retries),
            maxRepeaters: max(0, $maxRepeaters),
            maxOid: max(1, $configuredMaxOid),
            bulk: filter_var($rawBulk, FILTER_VALIDATE_BOOLEAN),
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isAvailable(Device $device, bool $commit = false): bool
    {
        $response = SnmpQuery::device($device)->get('SNMPv2-MIB::sysObjectID.0');

        return $response->getExitCode() === 0 || $response->getExitCode() === 2 || $response->isValid();
    }

    /**
     * @return array<int, string>
     */
    public function toNetSnmpOptions(?string $context = null): array
    {
        $options = ['-' . $this->version];

        if ($this->version === 'v3') {
            if ($this->authname !== null) {
                array_push($options, '-u', $this->authname);
            }

            array_push($options, '-l', $this->authlevel);

            if (in_array($this->authlevel, ['authNoPriv', 'authPriv'])) {
                array_push($options, '-a', $this->authalgo);

                if ($this->authpass !== null) {
                    array_push($options, '-A', $this->authpass);
                }
            }

            if ($this->authlevel === 'authPriv') {
                array_push($options, '-x', $this->cryptoalgo);

                if ($this->cryptopass !== null) {
                    array_push($options, '-X', $this->cryptopass);
                }
            }

            $resolvedContext = $context ?? $this->context;
            if ($resolvedContext !== null) {
                array_push($options, '-n', $resolvedContext);
            }
        } else {
            if ($this->community !== null) {
                array_push($options, '-c', $this->community);
            }
        }

        return $options;
    }

    public static function fromModel(DevicePollingMethod $method): static
    {
        return self::fromDevice($method->device); // TODO fix up
    }
}
