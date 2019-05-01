<?php
/**
 * Rewrite.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Device;

class Rewrite
{
    public static function normalizeIfType($type)
    {
        $rewrite_iftype = [
            'frameRelay'             => 'Frame Relay',
            'ethernetCsmacd'         => 'Ethernet',
            'softwareLoopback'       => 'Loopback',
            'tunnel'                 => 'Tunnel',
            'propVirtual'            => 'Virtual Int',
            'ppp'                    => 'PPP',
            'ds1'                    => 'DS1',
            'pos'                    => 'POS',
            'sonet'                  => 'SONET',
            'slip'                   => 'SLIP',
            'mpls'                   => 'MPLS Layer',
            'l2vlan'                 => 'VLAN Subif',
            'atm'                    => 'ATM',
            'aal5'                   => 'ATM AAL5',
            'atmSubInterface'        => 'ATM Subif',
            'propPointToPointSerial' => 'PtP Serial',
        ];

        if (isset($rewrite_iftype[$type])) {
            return $rewrite_iftype[$type];
        }

        return $type;
    }

    public static function normalizeIfName($name)
    {
        $rewrite_ifname = [
            'ether'                                          => 'Ether',
            'gig'                                            => 'Gig',
            'fast'                                           => 'Fast',
            'ten'                                            => 'Ten',
            '-802.1q vlan subif'                             => '',
            '-802.1q'                                        => '',
            'bvi'                                            => 'BVI',
            'vlan'                                           => 'Vlan',
            'tunnel'                                         => 'Tunnel',
            'serial'                                         => 'Serial',
            '-aal5 layer'                                    => ' aal5',
            'null'                                           => 'Null',
            'atm'                                            => 'ATM',
            'port-channel'                                   => 'Port-Channel',
            'dial'                                           => 'Dial',
            'hp procurve switch software loopback interface' => 'Loopback Interface',
            'control plane interface'                        => 'Control Plane',
            'loop'                                           => 'Loop',
            'bundle-ether'                                   => 'Bundle-Ether',
        ];

        return str_ireplace(array_keys($rewrite_ifname), array_values($rewrite_ifname), $name);
    }

    public static function shortenIfName($name)
    {
        $rewrite_shortif = [
            'tengigabitethernet'  => 'Te',
            'ten-gigabitethernet' => 'Te',
            'tengige'             => 'Te',
            'gigabitethernet'     => 'Gi',
            'fastethernet'        => 'Fa',
            'ethernet'            => 'Et',
            'serial'              => 'Se',
            'pos'                 => 'Pos',
            'port-channel'        => 'Po',
            'atm'                 => 'Atm',
            'null'                => 'Null',
            'loopback'            => 'Lo',
            'dialer'              => 'Di',
            'vlan'                => 'Vlan',
            'tunnel'              => 'Tunnel',
            'serviceinstance'     => 'SI',
            'dwdm'                => 'DWDM',
            'bundle-ether'        => 'BE',
            'bridge-aggregation'  => 'BA',
        ];

        return str_ireplace(array_keys($rewrite_shortif), array_values($rewrite_shortif), $name);
    }

    /**
     * Reformat a mac stored in the DB (only hex) to a nice readable format
     *
     * @param $mac
     * @return string
     */
    public static function readableMac($mac)
    {
        return rtrim(chunk_split($mac, 2, ':'), ':');
    }

    /**
     * Reformat hex MAC as oid MAC (dotted-decimal)
     *
     * 00:12:34:AB:CD:EF becomes 0.18.52.171.205.239
     * 0:12:34:AB:CD:EF  becomes 0.18.52.171.205.239
     * 00:02:04:0B:0D:0F becomes 0.2.4.11.13.239
     * 0:2:4:B:D:F       becomes 0.2.4.11.13.15
     *
     * @param string $mac
     * @return string oid representation of a MAC address
     */
    public static function oidMac($mac)
    {
        return implode('.', array_map('hexdec', explode(':', $mac)));
    }

    /**
     * Reformat Hex MAC with delimiters to Hex String without delimiters
     *
     * Assumes the MAC address is well-formed and in a common format.
     * 00:12:34:ab:cd:ef becomes 001234abcdef
     * 00:12:34:AB:CD:EF becomes 001234ABCDEF
     * 0:12:34:AB:CD:EF  becomes 001234ABCDEF
     * 00-12-34-AB-CD-EF becomes 001234ABCDEF
     * 001234-ABCDEF     becomes 001234ABCDEF
     * 0012.34AB.CDEF    becomes 001234ABCDEF
     * 00:02:04:0B:0D:0F becomes 0002040B0D0F
     * 0:2:4:B:D:F       becomes 0002040B0D0F
     *
     * @param string $mac hexadecimal MAC address with or without common delimiters
     * @return string undelimited hexadecimal MAC address
     */
    public static function macToHex($mac)
    {
        $mac_array = explode(':', str_replace(['-','.'], ':', $mac));
        $mac_padding = array_fill(0, count($mac_array), 12/count($mac_array));

        return implode(array_map('zeropad', $mac_array, $mac_padding));
    }

    /**
     * Make Cisco hardware human readable
     *
     * @param Device $device
     * @param bool $short
     * @return string
     */
    public static function ciscoHardware(&$device, $short = false)
    {
        if ($device['os'] == "ios") {
            if ($device['hardware']) {
                if (preg_match("/^WS-C([A-Za-z0-9]+)/", $device['hardware'], $matches)) {
                    if (!$short) {
                        $device['hardware'] = "Catalyst " . $matches[1] . " (" . $device['hardware'] . ")";
                    } else {
                        $device['hardware'] = "Catalyst " . $matches[1];
                    }
                } elseif (preg_match("/^CISCO([0-9]+)(.*)/", $device['hardware'], $matches)) {
                    if (!$short && $matches[2]) {
                        $device['hardware'] = "Cisco " . $matches[1] . " (" . $device['hardware'] . ")";
                    } else {
                        $device['hardware'] = "Cisco " . $matches[1];
                    }
                }
            } elseif (preg_match("/Cisco IOS Software, C([A-Za-z0-9]+) Software.*/", $device['sysDescr'], $matches)) {
                $device['hardware'] = "Catalyst " . $matches[1];
            } elseif (preg_match("/Cisco IOS Software, ([0-9]+) Software.*/", $device['sysDescr'], $matches)) {
                $device['hardware'] = "Cisco " . $matches[1];
            }
        }

        return $device['hardware'];
    }
}
