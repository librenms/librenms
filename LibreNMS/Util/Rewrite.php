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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Device;
use Cache;
use LibreNMS\Config;

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

    public static function shortenIfType($type)
    {
        return str_ireplace(
            [
                'FastEthernet',
                'TenGigabitEthernet',
                'GigabitEthernet',
                'Port-Channel',
                'Ethernet',
                'Bundle-Ether',
            ],
            [
                'Fa',
                'Te',
                'Gi',
                'Po',
                'Eth',
                'BE',
            ],
            $type
        );
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
     * @param string $mac
     * @return string
     */
    public static function readableMac($mac)
    {
        return rtrim(chunk_split($mac, 2, ':'), ':');
    }

    /**
     * Extract the OUI and match it against cached values
     *
     * @param string $mac
     * @return string
     */
    public static function readableOUI($mac)
    {
        $key = 'OUIDB-' . (substr($mac, 0, 6));

        return Cache::get($key, '');
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
        $mac_array = explode(':', str_replace(['-', '.'], ':', $mac));
        $mac_padding = array_fill(0, count($mac_array), 12 / count($mac_array));

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
        if ($device['os'] == 'ios') {
            if ($device['hardware']) {
                if (preg_match('/^WS-C([A-Za-z0-9]+)/', $device['hardware'], $matches)) {
                    if (! $short) {
                        $device['hardware'] = 'Catalyst ' . $matches[1] . ' (' . $device['hardware'] . ')';
                    } else {
                        $device['hardware'] = 'Catalyst ' . $matches[1];
                    }
                } elseif (preg_match('/^CISCO([0-9]+)(.*)/', $device['hardware'], $matches)) {
                    if (! $short && $matches[2]) {
                        $device['hardware'] = 'Cisco ' . $matches[1] . ' (' . $device['hardware'] . ')';
                    } else {
                        $device['hardware'] = 'Cisco ' . $matches[1];
                    }
                }
            } elseif (preg_match('/Cisco IOS Software, C([A-Za-z0-9]+) Software.*/', $device['sysDescr'], $matches)) {
                $device['hardware'] = 'Catalyst ' . $matches[1];
            } elseif (preg_match('/Cisco IOS Software, ([0-9]+) Software.*/', $device['sysDescr'], $matches)) {
                $device['hardware'] = 'Cisco ' . $matches[1];
            }
        }

        if ($device['os'] == 'iosxe') {
            if ($device['hardware']) {
                if (preg_match('/CAT9K/', $device['sysDescr'], $matches) && preg_match('/^C(9[A-Za-z0-9]+)/', $device['hardware'], $matches2)) {
                    if (! $short) {
                        $device['hardware'] = 'Catalyst ' . $matches2[1] . ' (' . $device['hardware'] . ')';
                    } else {
                        $device['hardware'] = 'Catalyst ' . $matches2[1];
                    }
                }
            }
        }

        return $device['hardware'];
    }

    public static function location($location)
    {
        $location = str_replace(["\n", '"'], '', $location);

        if (is_array(Config::get('location_map_regex'))) {
            foreach (Config::get('location_map_regex') as $reg => $val) {
                if (preg_match($reg, $location)) {
                    $location = $val;
                    break;
                }
            }
        }

        if (is_array(Config::get('location_map_regex_sub'))) {
            foreach (Config::get('location_map_regex_sub') as $reg => $val) {
                if (preg_match($reg, $location)) {
                    $location = preg_replace($reg, $val, $location);
                    break;
                }
            }
        }

        if (Config::has("location_map.$location")) {
            $location = Config::get("location_map.$location");
        }

        return $location;
    }

    public static function vmwareGuest($guest_id)
    {
        $guests = [
            'asianux3_64Guest'        => 'Asianux Server 3 (64 bit)',
            'asianux3Guest'           => 'Asianux Server 3',
            'asianux4_64Guest'        => 'Asianux Server 4 (64 bit)',
            'asianux4Guest'           => 'Asianux Server 4',
            'asianux5_64Guest'        => 'Asianux Server 5 (64 bit)',
            'asianux7_64Guest'        => 'Asianux Server 7 (64 bit)',
            'asianux8_64Guest'        => 'Asianux Server 8 (64 bit)',
            'centos6_64Guest'         => 'CentOS 6 (64-bit)',
            'centos64Guest'           => 'CentOS 4/5 (64-bit)',
            'centos6Guest'            => 'CentOS 6',
            'centos7_64Guest'         => 'CentOS 7 (64-bit)',
            'centos7Guest'            => 'CentOS 7',
            'centos8_64Guest'         => 'CentOS 8 (64-bit)',
            'centosGuest'             => 'CentOS 4/5',
            'coreos64Guest'           => 'CoreOS Linux (64 bit)',
            'darwin10_64Guest'        => 'Mac OS 10.6 (64 bit)',
            'darwin10Guest'           => 'Mac OS 10.6',
            'darwin11_64Guest'        => 'Mac OS 10.7 (64 bit)',
            'darwin11Guest'           => 'Mac OS 10.7',
            'darwin12_64Guest'        => 'Mac OS 10.8 (64 bit)',
            'darwin13_64Guest'        => 'Mac OS 10.9 (64 bit)',
            'darwin14_64Guest'        => 'Mac OS 10.10 (64 bit)',
            'darwin15_64Guest'        => 'Mac OS 10.11 (64 bit)',
            'darwin16_64Guest'        => 'Mac OS 10.12 (64 bit)',
            'darwin17_64Guest'        => 'macOS 10.13 (64 bit)',
            'darwin18_64Guest'        => 'macOS 10.14 (64 bit)',
            'darwin64Guest'           => 'Mac OS 10.5 (64 bit)',
            'darwinGuest'             => 'Mac OS 10.5',
            'debian10_64Guest'        => 'Debian GNU/Linux 10 (64 bit)',
            'debian10Guest'           => 'Debian GNU/Linux 10',
            'debian4_64Guest'         => 'Debian GNU/Linux 4 (64 bit)',
            'debian4Guest'            => 'Debian GNU/Linux 4',
            'debian5_64Guest'         => 'Debian GNU/Linux 5 (64 bit)',
            'debian5Guest'            => 'Debian GNU/Linux 5',
            'debian6_64Guest'         => 'Debian GNU/Linux 6 (64 bit)',
            'debian6Guest'            => 'Debian GNU/Linux 6',
            'debian7_64Guest'         => 'Debian GNU/Linux 7 (64 bit)',
            'debian7Guest'            => 'Debian GNU/Linux 7',
            'debian8_64Guest'         => 'Debian GNU/Linux 8 (64 bit)',
            'debian8Guest'            => 'Debian GNU/Linux 8',
            'debian9_64Guest'         => 'Debian GNU/Linux 9 (64 bit)',
            'debian9Guest'            => 'Debian GNU/Linux 9',
            'dosGuest'                => 'MS-DOS.',
            'eComStation2Guest'       => 'eComStation 2.0',
            'eComStationGuest'        => 'eComStation 1.x',
            'fedora64Guest'           => 'Fedora Linux (64 bit)',
            'fedoraGuest'             => 'Fedora Linux',
            'freebsd11_64Guest'       => 'FreeBSD 11 x64',
            'freebsd11Guest'          => 'FreeBSD 11',
            'freebsd12_64Guest'       => 'FreeBSD 12 x64',
            'freebsd12Guest'          => 'FreeBSD 12',
            'freebsd64Guest'          => 'FreeBSD x64',
            'freebsdGuest'            => 'FreeBSD',
            'genericLinuxGuest'       => 'Other Linux',
            'mandrakeGuest'           => 'Mandrake Linux',
            'mandriva64Guest'         => 'Mandriva Linux (64 bit)',
            'mandrivaGuest'           => 'Mandriva Linux',
            'netware4Guest'           => 'Novell NetWare 4',
            'netware5Guest'           => 'Novell NetWare 5.1',
            'netware6Guest'           => 'Novell NetWare 6.x',
            'nld9Guest'               => 'Novell Linux Desktop 9',
            'oesGuest'                => 'Open Enterprise Server',
            'openServer5Guest'        => 'SCO OpenServer 5',
            'openServer6Guest'        => 'SCO OpenServer 6',
            'opensuse64Guest'         => 'OpenSUSE Linux (64 bit)',
            'opensuseGuest'           => 'OpenSUSE Linux',
            'oracleLinux6_64Guest'    => 'Oracle 6 (64-bit)',
            'oracleLinux64Guest'      => 'Oracle Linux 4/5 (64-bit)',
            'oracleLinux6Guest'       => 'Oracle 6',
            'oracleLinux7_64Guest'    => 'Oracle 7 (64-bit)',
            'oracleLinux7Guest'       => 'Oracle 7',
            'oracleLinux8_64Guest'    => 'Oracle 8 (64-bit)',
            'oracleLinuxGuest'        => 'Oracle Linux 4/5',
            'os2Guest'                => 'OS/2',
            'other24xLinux64Guest'    => 'Linux 2.4x Kernel (64 bit) (experimental)',
            'other24xLinuxGuest'      => 'Linux 2.4x Kernel',
            'other26xLinux64Guest'    => 'Linux 2.6x Kernel (64 bit) (experimental)',
            'other26xLinuxGuest'      => 'Linux 2.6x Kernel',
            'other3xLinux64Guest'     => 'Linux 3.x Kernel (64 bit)',
            'other3xLinuxGuest'       => 'Linux 3.x Kernel',
            'other4xLinux64Guest'     => 'Linux 4.x Kernel (64 bit)',
            'other4xLinuxGuest'       => 'Linux 4.x Kernel',
            'otherGuest'              => 'Other Operating System',
            'otherGuest64'            => 'Other Operating System (64 bit) (experimental)',
            'otherLinux64Guest'       => 'Linux (64 bit) (experimental)',
            'otherLinuxGuest'         => 'Linux 2.2x Kernel',
            'redhatGuest'             => 'Red Hat Linux 2.1',
            'rhel2Guest'              => 'Red Hat Enterprise Linux 2',
            'rhel3_64Guest'           => 'Red Hat Enterprise Linux 3 (64 bit)',
            'rhel3Guest'              => 'Red Hat Enterprise Linux 3',
            'rhel4_64Guest'           => 'Red Hat Enterprise Linux 4 (64 bit)',
            'rhel4Guest'              => 'Red Hat Enterprise Linux 4',
            'rhel5_64Guest'           => 'Red Hat Enterprise Linux 5 (64 bit) (experimental)',
            'rhel5Guest'              => 'Red Hat Enterprise Linux 5',
            'rhel6_64Guest'           => 'Red Hat Enterprise Linux 6 (64 bit)',
            'rhel6Guest'              => 'Red Hat Enterprise Linux 6',
            'rhel7_64Guest'           => 'Red Hat Enterprise Linux 7 (64 bit)',
            'rhel7Guest'              => 'Red Hat Enterprise Linux 7',
            'rhel8_64Guest'           => 'Red Hat Enterprise Linux 8 (64 bit)',
            'sjdsGuest'               => 'Sun Java Desktop System',
            'sles10_64Guest'          => 'Suse Linux Enterprise Server 10 (64 bit) (experimental)',
            'sles10Guest'             => 'Suse linux Enterprise Server 10',
            'sles11_64Guest'          => 'Suse Linux Enterprise Server 11 (64 bit)',
            'sles11Guest'             => 'Suse linux Enterprise Server 11',
            'sles12_64Guest'          => 'Suse Linux Enterprise Server 12 (64 bit)',
            'sles12Guest'             => 'Suse linux Enterprise Server 12',
            'sles15_64Guest'          => 'Suse Linux Enterprise Server 15 (64 bit)',
            'sles64Guest'             => 'Suse Linux Enterprise Server 9 (64 bit)',
            'slesGuest'               => 'Suse Linux Enterprise Server 9',
            'solaris10_64Guest'       => 'Solaris 10 (64 bit) (experimental)',
            'solaris10Guest'          => 'Solaris 10 (32 bit) (experimental)',
            'solaris11_64Guest'       => 'Solaris 11 (64 bit)',
            'solaris6Guest'           => 'Solaris 6',
            'solaris7Guest'           => 'Solaris 7',
            'solaris8Guest'           => 'Solaris 8',
            'solaris9Guest'           => 'Solaris 9',
            'suse64Guest'             => 'Suse Linux (64 bit)',
            'suseGuest'               => 'Suse Linux',
            'turboLinux64Guest'       => 'Turbolinux (64 bit)',
            'turboLinuxGuest'         => 'Turbolinux',
            'ubuntu64Guest'           => 'Ubuntu Linux (64 bit)',
            'ubuntuGuest'             => 'Ubuntu Linux',
            'unixWare7Guest'          => 'SCO UnixWare 7',
            'vmkernel5Guest'          => 'VMware ESX 5',
            'vmkernel65Guest'         => 'VMware ESX 6.5',
            'vmkernel6Guest'          => 'VMware ESX 6',
            'vmkernelGuest'           => 'VMware ESX 4',
            'vmwarePhoton64Guest'     => 'VMware Photon (64 bit)',
            'win2000AdvServGuest'     => 'Windows 2000 Advanced Server',
            'win2000ProGuest'         => 'Windows 2000 Professional',
            'win2000ServGuest'        => 'Windows 2000 Server',
            'win31Guest'              => 'Windows 3.1',
            'win95Guest'              => 'Windows 95',
            'win98Guest'              => 'Windows 98',
            'windows7_64Guest'        => 'Windows 7 (64 bit)',
            'windows7Guest'           => 'Windows 7',
            'windows7Server64Guest'   => 'Windows Server 2008 R2 (64 bit)',
            'windows8_64Guest'        => 'Windows 8 (64 bit)',
            'windows8Guest'           => 'Windows 8',
            'windows8Server64Guest'   => 'Windows 8 Server (64 bit)',
            'windows9_64Guest'        => 'Windows 10 (64 bit)',
            'windows9Guest'           => 'Windows 10',
            'windows9Server64Guest'   => 'Windows 10 Server (64 bit)',
            'windowsHyperVGuest'      => 'Windows Hyper-V',
            'winLonghorn64Guest'      => 'Windows Longhorn (64 bit) (experimental)',
            'winLonghornGuest'        => 'Windows Longhorn (experimental)',
            'winMeGuest'              => 'Windows Millenium Edition',
            'winNetBusinessGuest'     => 'Windows Small Business Server 2003',
            'winNetDatacenter64Guest' => 'Windows Server 2003, Datacenter Edition (64 bit) (experimental)',
            'winNetDatacenterGuest'   => 'Windows Server 2003, Datacenter Edition',
            'winNetEnterprise64Guest' => 'Windows Server 2003, Enterprise Edition (64 bit)',
            'winNetEnterpriseGuest'   => 'Windows Server 2003, Enterprise Edition',
            'winNetStandard64Guest'   => 'Windows Server 2003, Standard Edition (64 bit)',
            'winNetStandardGuest'     => 'Windows Server 2003, Standard Edition',
            'winNetWebGuest'          => 'Windows Server 2003, Web Edition',
            'winNTGuest'              => 'Windows NT 4',
            'winVista64Guest'         => 'Windows Vista (64 bit)',
            'winVistaGuest'           => 'Windows Vista',
            'winXPHomeGuest'          => 'Windows XP Home Edition',
            'winXPPro64Guest'         => 'Windows XP Professional Edition (64 bit)',
            'winXPProGuest'           => 'Windows XP Professional',
        ];

        return $guests[$guest_id] ?? $guest_id;
    }

    public static function zeropad($num, $length = 2)
    {
        return str_pad($num, $length, '0', STR_PAD_LEFT);
    }
}
