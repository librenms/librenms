<?php
/**
 * IP.php
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

abstract class IP
{
    public $ip;
    public $cidr;
    public $host_bits;

    /**
     * Convert a hex-string to an IP address. For example: "c0 a8 01 fe" -> 192.168.1.254
     * @param string $hex
     * @param bool $ignore_errors Do not throw exceptions, instead return null on error.
     * @return IP|null
     * @throws InvalidIpException
     */
    public static function fromHexString($hex, $ignore_errors = false)
    {
        $hex = str_replace([' ', '"'], '', $hex);

        try {
            return self::parse($hex);
        } catch (InvalidIpException $e) {
            // ignore
        }

        $hex = str_replace([':', '.'], '', $hex);

        try {
            if (strlen($hex) == 8) {
                return new IPv4(long2ip(hexdec($hex)));
            }

            if (strlen($hex) == 32) {
                return new IPv6(implode(':', str_split($hex, 4)));
            }
        } catch (InvalidIpException $e) {
            if (! $ignore_errors) {
                throw $e;
            }
        }

        if (! $ignore_errors) {
            throw new InvalidIpException("Could not parse into IP: $hex");
        }

        return null;
    }

    /**
     * Convert a decimal space-separated string to an IP address. For example:
     * "192 168 1 154" -> 192.168.1.254
     * "32 01 72 96 72 96 00 00 00 00 00 00 00 00 136 136" -> 2001:4860:4860::8888
     * @param string $snmpOid
     * @param bool $ignore_errors Do not throw exceptions, instead return null on error.
     * @return IP|null
     * @throws InvalidIpException
     */
    public static function fromSnmpString($snmpOid, $ignore_errors = false)
    {
        $snmpOid = str_replace(['.', '"'], ' ', $snmpOid);
        $hex = implode(
            ':',
            array_map(
                function ($dec) {
                    return sprintf('%02x', $dec);
                },
                explode(' ', (string) $snmpOid)
            )
        );

        return IP::fromHexString($hex, $ignore_errors);
    }

    /**
     * Parse an IP or IP Network into an IP object. Works with IPv6 and IPv4 addresses.
     * @param string $ip
     * @param bool $ignore_errors Do not throw exceptions, instead return null on error.
     * @return IP|null
     * @throws InvalidIpException
     */
    public static function parse($ip, $ignore_errors = false)
    {
        try {
            return new IPv4($ip);
        } catch (InvalidIpException $e) {
            // ignore ipv4 failure and try ipv6
        }

        try {
            return new IPv6($ip);
        } catch (InvalidIpException $e) {
            if (! $ignore_errors) {
                throw new InvalidIpException("$ip is not a valid IP address");
            }
        }

        return null;
    }

    /**
     * Check if the supplied IP is valid.
     * @param string $ip
     * @param bool $exclude_reserved Exclude reserved IP ranges.
     * @return bool
     */
    public static function isValid($ip, $exclude_reserved = false)
    {
        return IPv4::isValid($ip, $exclude_reserved) || IPv6::isValid($ip, $exclude_reserved);
    }

    /**
     * Get the network of this IP in cidr format.
     * @param int $cidr If not given will use the cidr stored with this IP
     * @return string
     */
    public function getNetwork($cidr = null)
    {
        if (is_null($cidr)) {
            $cidr = $this->cidr;
        }

        return $this->getNetworkAddress($cidr) . "/$cidr";
    }

    /**
     * Get the network address of this IP
     * @param int $cidr If not given will use the cidr stored with this IP
     * @return string
     */
    abstract public function getNetworkAddress($cidr = null);

    /**
     * Check if this IP address is contained inside the network
     * @param string $network should be in cidr format.
     * @return mixed
     */
    abstract public function inNetwork($network);

    /**
     * Check if this IP is in one of multiple networks
     *
     * @param array $networks
     * @return bool
     */
    public function inNetworks($networks)
    {
        foreach ((array) $networks as $network) {
            if ($this->inNetwork($network)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this IP is in the reserved range.
     * @return bool
     */
    public function isReserved()
    {
        return filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Remove extra 0s from this IPv6 address to make it easier to read.
     * IPv4 addresses, just return the address.
     * @return string|false
     */
    public function compressed()
    {
        return (string) $this->ip;
    }

    /**
     * Expand this IPv6 address to it's full IPv6 representation. For example: ::1 -> 0000:0000:0000:0000:0000:0000:0000:0001
     * IPv4 addresses, just return the address.
     * @return string
     */
    public function uncompressed()
    {
        return (string) $this->ip;
    }

    /**
     * Packed address for storage in database
     *
     * return string
     */
    public function packed()
    {
        return inet_pton((string) $this->ip);
    }

    /**
     * Get the family of this IP.
     * @return string ipv4 or ipv6
     */
    public function getFamily()
    {
        return $this->host_bits == 32 ? 'ipv4' : 'ipv6';
    }

    public function __toString()
    {
        if ($this->cidr == $this->host_bits) {
            return (string) $this->ip;
        }

        return $this->ip . "/{$this->cidr}";
    }

    /**
     * Extract an address from a cidr, assume a host is given if it does not contain /
     * @param string $ip
     * @return array [$ip, $cidr]
     */
    protected function extractCidr($ip)
    {
        return array_pad(explode('/', $ip, 2), 2, $this->host_bits);
    }

    /**
     * Convert this IP to an snmp index hex encoded
     *
     * @return string
     */
    abstract public function toSnmpIndex();
}
