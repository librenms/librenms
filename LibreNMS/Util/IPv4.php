<?php
/**
 * IPv4.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Exceptions\InvalidIpException;

class IPv4 extends IP
{
    /**
     * IPv4 constructor.
     * @param string $ipv4
     * @throws InvalidIpException
     */
    public function __construct($ipv4)
    {
        $this->host_bits = 32;
        [$this->ip, $this->cidr] = $this->extractCidr($ipv4);

        if (! self::isValid($this->ip)) {
            throw new InvalidIpException("$ipv4 is not a valid ipv4 address");
        }
    }

    /**
     * Check if the supplied IP is valid.
     * @param string $ipv4
     * @param bool $exclude_reserved Exclude reserved IP ranges.
     * @return bool
     */
    public static function isValid($ipv4, $exclude_reserved = false)
    {
        $filter = FILTER_FLAG_IPV4;
        if ($exclude_reserved) {
            $filter |= FILTER_FLAG_NO_RES_RANGE;
        }

        return filter_var($ipv4, FILTER_VALIDATE_IP, $filter) !== false;
    }

    /**
     * Convert an IPv4 network mask to a bit mask.  For example: 255.255.255.0 -> 24
     * @param string $netmask
     * @return int
     */
    public static function netmask2cidr($netmask)
    {
        $long = ip2long($netmask);
        $base = ip2long('255.255.255.255');

        return (int) (32 - log(($long ^ $base) + 1, 2));
    }

    /**
     * Returns the netmask of this IP address. For example: 255.255.255.0
     * @return string
     */
    public function getNetmask()
    {
        return long2ip($this->cidr2long($this->cidr));
    }

    /**
     * Convert an IPv4 bit mask to a long. Generally used with long2ip() or bitwise operations.
     * @return int
     */
    private function cidr2long($cidr)
    {
        return -1 << (32 - (int) $cidr);
    }

    /**
     * Check if this IP address is contained inside the network.
     * @param string $network should be in cidr format.
     * @return mixed
     */
    public function inNetwork($network)
    {
        [$net, $cidr] = $this->extractCidr($network);
        if (! self::isValid($net)) {
            return false;
        }

        $mask = $this->cidr2long($cidr);

        return (ip2long($this->ip) & $mask) == (ip2long($net) & $mask);
    }

    /**
     * Get the network address of this IP
     * @param int $cidr if not given will use the cidr stored with this IP
     * @return string
     */
    public function getNetworkAddress($cidr = null)
    {
        if (is_null($cidr)) {
            $cidr = $this->cidr;
        }

        return long2ip(ip2long($this->ip) & $this->cidr2long($cidr));
    }

    /**
     * Convert this IP to an snmp index hex encoded
     *
     * @return string
     */
    public function toSnmpIndex()
    {
        return (string) $this->ip;
    }
}
