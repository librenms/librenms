<?php

/**
 * Neighbor.php
 *
 * A neighbor learned from a discovery protocol (LLDP, CDP, FDP, etc.)
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

namespace LibreNMS\Discovery\Neighbors;

use Illuminate\Support\Str;
use LibreNMS\Enum\LldpChassisIdSubtype;
use LibreNMS\Enum\LldpPortIdSubtype;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;
use LibreNMS\Util\StringHelpers;

final readonly class Neighbor
{
    public ?string $portMac;

    /**
     * @param  string  $protocol  lldp, cdp, fdp, etc
     * @param  int|null  $localPortId  port_id of the port on this device the neighbor was seen on
     * @param  string  $sysName  the name the neighbor advertised for itself
     * @param  string  $sysDescr  the neighbor's description or software version
     * @param  string|null  $platform  the neighbor's hardware platform
     * @param  string|null  $managementIp  an ip address the neighbor may be reached at
     * @param  string|null  $chassisMac  the neighbor's chassis mac address as 12 hex characters
     * @param  string  $portId  the neighbor's port identifier, interpreted according to $portIdSubtype (mac addresses as 12 hex characters)
     * @param  string  $portDescr  the neighbor's port description
     * @param  string|null  $portMac  the neighbor's port mac address as 12 hex characters, defaults to the port id if it is a mac address
     */
    public function __construct(
        public string $protocol,
        public ?int $localPortId,
        public string $sysName = '',
        public string $sysDescr = '',
        public ?string $platform = null,
        public ?string $managementIp = null,
        public ?string $chassisMac = null,
        public string $portId = '',
        public LldpPortIdSubtype $portIdSubtype = LldpPortIdSubtype::InterfaceName,
        public string $portDescr = '',
        ?string $portMac = null,
    ) {
        $this->portMac = $portMac ?? ($portIdSubtype === LldpPortIdSubtype::MacAddress ? self::parseMac($portId) : null);
    }

    /**
     * Create from an LLDP-MIB lldpRemTable row (column names without the MIB prefix).
     * Vendor copies of the LLDP-MIB that share the same column names work too.
     *
     * @param  array<string, mixed>  $entry
     */
    public static function fromLldpRemEntry(array $entry, ?int $localPortId, ?string $managementIp = null): self
    {
        $chassisIdSubtype = LldpChassisIdSubtype::parse($entry['lldpRemChassisIdSubtype'] ?? null);
        $chassisId = (string) ($entry['lldpRemChassisId'] ?? '');
        $portIdSubtype = LldpPortIdSubtype::parse($entry['lldpRemPortIdSubtype'] ?? null);

        if ($managementIp === null && $chassisIdSubtype === LldpChassisIdSubtype::NetworkAddress) {
            $managementIp = self::parseNetworkAddress($chassisId);
        }

        $portId = self::parsePortId((string) ($entry['lldpRemPortId'] ?? ''), $portIdSubtype);
        $portMac = $portIdSubtype === LldpPortIdSubtype::MacAddress ? self::parseMac($portId) : null;

        // Some neighbors (VMware dvSwitch) send the port name as the chassis id and the port mac as the port id
        if (($portMac !== null || $portId === '') && in_array($chassisIdSubtype, [LldpChassisIdSubtype::InterfaceName, LldpChassisIdSubtype::InterfaceAlias])) {
            $portId = self::parseText($chassisId);
            $portIdSubtype = $chassisIdSubtype === LldpChassisIdSubtype::InterfaceName ? LldpPortIdSubtype::InterfaceName : LldpPortIdSubtype::InterfaceAlias;
        }

        return new self(
            protocol: 'lldp',
            localPortId: $localPortId,
            sysName: self::parseName($entry['lldpRemSysName'] ?? ''),
            sysDescr: self::parseText($entry['lldpRemSysDesc'] ?? ''),
            managementIp: $managementIp,
            chassisMac: $chassisIdSubtype === LldpChassisIdSubtype::MacAddress ? self::parseMac($chassisId) : null,
            portId: $portId,
            portIdSubtype: $portIdSubtype,
            portDescr: self::parseText($entry['lldpRemPortDesc'] ?? ''),
            portMac: $portMac,
        );
    }

    /**
     * Human-readable name of the neighbor's port
     */
    public function portLabel(): string
    {
        if ($this->portIdSubtype === LldpPortIdSubtype::MacAddress && $this->portMac !== null) {
            return Mac::parse($this->portMac)->readable();
        }

        return $this->portId !== '' ? $this->portId : $this->portDescr;
    }

    /**
     * Parse a mac address from snmp, which may be hex (with various delimiters) or raw bytes
     *
     * @return string|null 12 hex characters
     */
    public static function parseMac(?string $value): ?string
    {
        $value = trim((string) $value);
        $mac = Mac::parse(str_replace(' ', ':', $value));

        if (! $mac->isValid() && strlen($value) == 6) {
            $mac = Mac::parse(bin2hex($value)); // net-snmp printed the bytes as a string
        }

        return $mac->isValid() && $mac->hex() !== '000000000000' ? $mac->hex() : null;
    }

    /**
     * Parse an ip from a LLDP network address (IANA address family followed by the address) or a plain ip
     */
    public static function parseNetworkAddress(?string $value): ?string
    {
        $value = trim((string) $value);
        $hex = str_replace([' ', ':'], '', $value);

        if (ctype_xdigit($hex) && in_array(strlen($hex), [10, 34])) {
            $ip = IP::fromHexString(substr($hex, 2), true);
        } else {
            $ip = IP::parse($value, true);
        }

        return $ip?->compressed();
    }

    /**
     * Clean up a text value from snmp
     */
    public static function parseText(mixed $value): string
    {
        $value = trim((string) $value);

        // a single hex byte is far more likely to be text than a non-printable character
        if (str_contains($value, ' ')) {
            $value = StringHelpers::decodeSnmpHexText($value);
        }

        // some devices pad text with null bytes and garbage after them
        return trim(Str::before($value, "\0"));
    }

    /**
     * Clean up a device name from snmp.
     * Some devices pad names with null bytes, which net-snmp may print as dots, followed by garbage.
     */
    public static function parseName(mixed $value): string
    {
        return rtrim(Str::before(self::parseText($value), "\n"), " .\r");
    }

    /**
     * Parse a port id according to its subtype, mac addresses are returned as 12 hex characters
     */
    public static function parsePortId(string $portId, LldpPortIdSubtype $subtype): string
    {
        return match ($subtype) {
            LldpPortIdSubtype::MacAddress => self::parseMac($portId) ?? self::parseText($portId),
            LldpPortIdSubtype::NetworkAddress => self::parseNetworkAddress($portId) ?? self::parseText($portId),
            default => self::parseText($portId),
        };
    }
}
