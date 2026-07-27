<?php
/**
 * SecretDefinition.php
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

namespace LibreNMS\Polling\Secrets;

use LibreNMS\Enum\SecretType;
use LibreNMS\Interfaces\SecretDefinitionInterface;
use LibreNMS\Polling\Secrets\Definitions\IpmiSecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;

class SecretDefinition
{
    public static function for(SecretType $type): SecretDefinitionInterface
    {
        return match($type) {
            SecretType::Ipmi => new IpmiSecretDefinition,
            SecretType::Snmp => new SnmpSecretDefinition,
        };
    }
}
