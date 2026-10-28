<?php

/**
 * SecretType.php
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

namespace LibreNMS\Enum;

use LibreNMS\Traits\HasUnknownCase;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;
use LibreNMS\Polling\Secrets\Data\SecretData;
use LibreNMS\Polling\Secrets\Data\SnmpCommunitySecretData;
use LibreNMS\Polling\Secrets\Data\SnmpV3SecretData;
use LibreNMS\Polling\Secrets\Data\UnknownSecretData;
use LibreNMS\Polling\Secrets\Definitions\IpmiSecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SnmpCommunitySecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SnmpV3SecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\UnknownSecretDefinition;

enum SecretType: string
{
    use HasUnknownCase;

    case SnmpCommunity = 'snmp-community';
    case SnmpV3 = 'snmp-v3';
    case Ipmi = 'ipmi';
    /** Stored types this version does not know, such as ones from a development branch */
    case Unknown = 'unknown';

    public function label(): string
    {
        return __('poller.secret_types.' . $this->value);
    }

    /**
     * The fields (form, validation rules) of this secret type.
     */
    public function definition(): SecretDefinition
    {
        return match ($this) {
            self::SnmpCommunity => new SnmpCommunitySecretDefinition,
            self::SnmpV3 => new SnmpV3SecretDefinition,
            self::Ipmi => new IpmiSecretDefinition,
            self::Unknown => new UnknownSecretDefinition,
        };
    }

    /**
     * The typed data of a secret of this type.
     *
     * @param  array<string, mixed>  $data
     */
    public function data(array $data): SecretData
    {
        return match ($this) {
            self::SnmpCommunity => SnmpCommunitySecretData::fromArray($data),
            self::SnmpV3 => SnmpV3SecretData::fromArray($data),
            self::Ipmi => IpmiSecretData::fromArray($data),
            self::Unknown => new UnknownSecretData($data),
        };
    }
}
