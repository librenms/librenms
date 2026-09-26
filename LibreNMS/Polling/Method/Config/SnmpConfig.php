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

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Support\Arr;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;

final class SnmpConfig extends PollingMethodConfig
{
    public function __construct(
        bool $enabled = true,
        bool $affectsAvailability = true,

        // Secrets
        public string $version = 'v2c',
        public ?string $community = null,
        public ?string $authname = null,
        public ?string $authpass = null,
        public ?string $authlevel = null,
        public ?string $authalgo = null,
        public ?string $cryptopass = null,
        public ?string $cryptoalgo = null,

        // Settings
        public string $transport = 'udp',
        public int $port = 161,
        public ?string $context = null,
        public int|float $timeout = 1,
        public int $retries = 5,
        public int $maxRepeaters = 0,
        public int $maxOid = 10,
        public bool $bulk = true,
        public string $portAssociationMode = 'ifIndex',
    ) {
        parent::__construct($enabled, $affectsAvailability);
    }

    public function isValid(): bool
    {
        if ($this->version === 'v3') {
            if ($this->authlevel === 'authNoPriv') {
                return ! empty($this->authname) && ! empty($this->authpass);
            }

            if ($this->authlevel === 'authPriv') {
                return ! empty($this->authname)
                    && ! empty($this->authpass)
                    && ! empty($this->cryptoalgo)
                    && ! empty($this->cryptopass);
            }

            return $this->authlevel === 'noAuthNoPriv';
        }

        if ($this->version === 'v2c' || $this->version === 'v1') {
            return ! empty($this->community);
        }

        return false;
    }

    public static function default(?string $os = 'generic'): self
    {
        $os = $os ?: 'generic';

        return new self(
            enabled: true,
            affectsAvailability: true,
            version: 'v2c',
            community: Arr::first(Arr::wrap(LibrenmsConfig::get('snmp.community', ['public']))) ?: 'public',
            transport: LibrenmsConfig::get('snmp.transports.0', 'udp'),
            port: (int) LibrenmsConfig::get('snmp.port', 161),
            timeout: (float) LibrenmsConfig::get('snmp.timeout', 1),
            retries: (int) LibrenmsConfig::get('snmp.retries', 5),
            maxRepeaters: max(0, (int) LibrenmsConfig::getOsSetting(
                $os,
                'snmp.max_repeaters',
                LibrenmsConfig::get('snmp.max_repeaters', 10)
            )),
            maxOid: max(1, (int) LibrenmsConfig::getOsSetting(
                $os,
                'snmp_max_oid',
                LibrenmsConfig::get('snmp.max_oid', 10)
            )),
            bulk: filter_var(
                LibrenmsConfig::getOsSetting(
                    $os,
                    'snmp_bulk',
                    LibrenmsConfig::get('snmp_bulk', true)
                ),
                FILTER_VALIDATE_BOOLEAN
            ),
            portAssociationMode: LibrenmsConfig::get('default_port_association_mode', 'ifIndex'),
        );
    }

    public static function fromSettings(
        array $settings,
        ?SnmpSecretData $secretData = null,
        ?string $os = 'generic',
        bool $enabled = true,
        bool $affectsAvailability = true,
    ): self {
        $default = self::default($os);
        $secretData ??= new SnmpSecretData();

        $portAssoc = $settings['port_association_mode']
            ?? (isset($settings['port_association_mode_id']) && is_numeric($settings['port_association_mode_id'])
                ? \LibreNMS\Enum\PortAssociationMode::getName((int) $settings['port_association_mode_id'])
                : null)
            ?? $default->portAssociationMode;

        return new self(
            enabled: $enabled,
            affectsAvailability: $affectsAvailability,
            version: $secretData->version,
            community: $secretData->community,
            authname: $secretData->authname,
            authpass: $secretData->authpass,
            authlevel: $secretData->authlevel,
            authalgo: $secretData->authalgo,
            cryptopass: $secretData->cryptopass,
            cryptoalgo: $secretData->cryptoalgo,
            transport: isset($settings['transport']) && $settings['transport'] !== '' ? (string) $settings['transport'] : $default->transport,
            port: isset($settings['port']) && is_numeric($settings['port']) ? (int) $settings['port'] : $default->port,
            context: $secretData->context,
            timeout: isset($settings['timeout']) && is_numeric($settings['timeout']) && $settings['timeout'] > 0 ? max(0.1, (float) $settings['timeout']) : $default->timeout,
            retries: isset($settings['retries']) && is_numeric($settings['retries']) ? max(0, (int) $settings['retries']) : $default->retries,
            maxRepeaters: isset($settings['max_repeaters']) && is_numeric($settings['max_repeaters']) ? max(0, (int) $settings['max_repeaters']) : $default->maxRepeaters,
            maxOid: isset($settings['max_oid']) && is_numeric($settings['max_oid']) ? max(1, (int) $settings['max_oid']) : $default->maxOid,
            bulk: (isset($settings['bulk']) || isset($settings['snmp_bulk']))
                ? filter_var($settings['bulk'] ?? $settings['snmp_bulk'], FILTER_VALIDATE_BOOLEAN)
                : $default->bulk,
            portAssociationMode: $portAssoc,
        );
    }

    public static function fromSettingsAndSecretData(
        array $settings,
        SnmpSecretData $secretData,
        ?string $os = 'generic',
        bool $enabled = true,
        bool $affectsAvailability = true,
    ): self {
        return self::fromSettings(
            settings: $settings,
            secretData: $secretData,
            os: $os,
            enabled: $enabled,
            affectsAvailability: $affectsAvailability,
        );
    }

    /**
     * Array representation of non-secret settings.
     *
     * @return array<string, mixed>
     */
    public function settingsArray(): array
    {
        return [
            'transport' => $this->transport,
            'port' => $this->port,
            'timeout' => $this->timeout,
            'retries' => $this->retries,
            'max_repeaters' => $this->maxRepeaters,
            'max_oid' => $this->maxOid,
            'bulk' => $this->bulk,
            'port_association_mode' => $this->portAssociationMode,
        ];
    }

    /**
     * Create from legacy fields. Emergency fallback, do not use.
     */
    public static function fromLegacyDeviceFields(Device $device): self
    {
        return self::fromSettingsAndSecretData(
            settings: [
                'transport' => $device->getAttribute('transport'),
                'port' => $device->getAttribute('port'),
                'timeout' => $device->getAttribute('timeout'),
                'retries' => $device->getAttribute('retries'),
                'max_repeaters' => $device->getAttrib('snmp_max_repeaters'),
                'max_oid' => $device->getAttrib('snmp_max_oid'),
                'bulk' => $device->getAttrib('snmp_bulk'),
                'port_association_mode' => $device->getAttribute('port_association_mode') !== null ? \LibreNMS\Enum\PortAssociationMode::getName((int) $device->getAttribute('port_association_mode')) : null,
            ],
            secretData: new SnmpSecretData(
                version: (string) ($device->getAttribute('snmpver') ?: 'v2c'),
                community: $device->getAttribute('community'),
                authlevel: $device->getAttribute('authlevel'),
                authname: $device->getAttribute('authname'),
                authpass: $device->getAttribute('authpass'),
                authalgo: $device->getAttribute('authalgo'),
                cryptoalgo: $device->getAttribute('cryptoalgo'),
                cryptopass: $device->getAttribute('cryptopass'),
            ),
            os: $device->os,
            enabled: ! ($device->getAttribute('snmp_disable') ?? false),
        );
    }

    public static function fromDeviceArray(?array $device): self
    {
        $device ??= [];
        $device_id = $device['device_id'] ?? 0;

        if (DeviceCache::has($device_id)) {
            return DeviceCache::get($device_id)->toSnmpConfig();
        }

        return self::fromSettingsAndSecretData(
            settings: [
                'transport' => $device['transport'] ?? null,
                'port' => $device['port'] ?? null,
                'timeout' => $device['timeout'] ?? null,
                'retries' => $device['retries'] ?? null,
                'max_repeaters' => $device['snmp_max_repeaters'] ?? null,
                'max_oid' => $device['snmp_max_oid'] ?? null,
                'bulk' => $device['snmp_bulk'] ?? null,
                'port_association_mode' => isset($device['port_association_mode'])
                    ? (is_numeric($device['port_association_mode']) ? \LibreNMS\Enum\PortAssociationMode::getName((int) $device['port_association_mode']) : $device['port_association_mode'])
                    : null,
            ],
            secretData: new SnmpSecretData(
                version: (string) ($device['snmpver'] ?? 'v2c'),
                community: isset($device['community'])
                    ? (string) $device['community']
                    : (Arr::first(Arr::wrap(LibrenmsConfig::get('snmp.community', ['public']))) ?: 'public'),
                authlevel: $device['authlevel'] ?? null,
                authname: $device['authname'] ?? null,
                authpass: $device['authpass'] ?? null,
                authalgo: $device['authalgo'] ?? null,
                cryptoalgo: $device['cryptoalgo'] ?? null,
                cryptopass: $device['cryptopass'] ?? null,
                context: $device['context_name'] ?? null,
            ),
            os: $device['os'] ?? null,
            enabled: ! ($device['snmp_disable'] ?? false),
        );
    }
}
