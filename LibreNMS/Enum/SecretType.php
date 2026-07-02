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

use LibreNMS\Polling\Secrets\IcmpSecret;
use LibreNMS\Polling\Secrets\IpmiSecretData;
use LibreNMS\Polling\Secrets\SecretData;
use LibreNMS\Polling\Secrets\SnmpSecretData;
use LibreNMS\Polling\Secrets\UnixAgentSecret;

enum SecretType: string
{
    case Snmp = 'snmp';
    case Ipmi = 'ipmi';

    /** @param class-string<SecretData> $class */
    public static function fromClass(string $class): self
    {
        foreach (self::cases() as $case) {
            if ($case->secretClass() === $class) {
                return $case;
            }
        }

        throw new \InvalidArgumentException("Unregistered secret class: $class");
    }

    /** @return class-string<SecretData> */
    public function secretClass(): string
    {
        return match ($this) {
            self::Snmp => SnmpSecretData::class,
            self::Ipmi => IpmiSecretData::class,
        };
    }

}
