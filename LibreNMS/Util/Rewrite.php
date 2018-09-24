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
        ];

        return str_ireplace(array_keys($rewrite_shortif), array_values($rewrite_shortif), $name);
    }
}
