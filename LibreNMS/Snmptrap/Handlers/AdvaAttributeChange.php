<?php
/**
 * AdvaAtributeChange.php
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
 * Attribute change traps show changes to Adva configuration values after they are submitted.
 * This handler only catches some of those changes and aims to provide the user with
 * information about what configuration module was modified.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class AdvaAttributeChange implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param Device $device
     * @param Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $device_array = $device->toArray();

        if ($trap->findOid('CM-SYSTEM-MIB::sysLog')) {
            $syslogEntry = substr($trap->findOid('CM-SYSTEM-MIB::sysLog'), -1);
            if ($trap->findOid('CM-SYSTEM-MIB::sysLogIpVersion')) {
                $ipVer = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogIpVersion'));
                log_event("Syslog server $syslogEntry IP version set to $ipVer.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-SYSTEM-MIB::sysLogIpAddress')) {
                $ipAddr = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogIpAddress'));
                log_event("Syslog server $syslogEntry IP address changed to $ipAddr.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-SYSTEM-MIB::sysLogPort')) {
                $syslogPort = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogPort'));
                log_event("Syslog server $syslogEntry IP address changed to $syslogPort.", $device_array, 'trap', 2);
            }
        } elseif ($trap->findOid('CM-SYSTEM-MIB::aclEntry')) {
            $aclEntry = substr($trap->findOid('CM-SYSTEM-MIB::aclEntry'), -1);
            log_event("ACL $aclEntry modified", $device_array, 'trap', 2);
        } elseif ($trap->findOid('CM-SYSTEM-MIB::securityBanner')) {
            log_event("MOTD/Banner modified.", $device_array, 'trap', 2);
        } elseif ($trap->findOid('F3-TIMEZONE-MIB::f3TimeZone')) {
            $enabled = $trap->getOidData($trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled'));
            if ('true' === $enabled && $trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled')) {
                log_event("Daylight Savings Time enabled.", $device_array, 'trap', 2);
            } elseif ('false' === $enabled && $trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled')) {
                log_event("Daylight Savings Time disabled.", $device_array, 'trap', 2);
            }
            if ($trap->findOid("F3-TIMEZONE-MIB::f3TimeZoneUtcOffset")) {
                $dstOffset = $trap->getOidData($trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneUtcOffset'));
                log_event("UTC offset (timezone) change to $dstOffset", $device_array, 'trap', 2);
            }
        } elseif ($trap->findOid('CM-SYSTEM-MIB::ntp')) {
            if ($trap->findOid('CM-SYSTEM-MIB::ntpPrimaryServer')) {
                $primaryIP = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::ntpPrimaryServer'));
                log_event("Primary NTP server IP $primaryIP", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-SYSTEM-MIB::ntpBackupServer')) {
                $backupIP = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::ntpBackupServer'));
                log_event("Backup NTP server IP $backupIP", $device_array, 'trap', 2);
            }
        } elseif ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServer')) {
            $serverEntry = substr($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServer'), -1);
            if ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerIpAddress')) {
                $serverIP = $trap->getOidData($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerIpAddress'));
                log_event("Authentication server $serverEntry IP changed to $serverIP", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerSecret')) {
                log_event("Authentication server $serverEntry secret changed.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerEnabled')) {
                $serverEnable = $trap->getOidData($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerEnabled'));
                if ('true' === $serverEnable) {
                    log_event("Authentication server $serverEntry enabled.", $device_array, 'trap', 2);
                } else {
                    log_event("Authentication server $serverEntry disabled.", $device_array, 'trap', 2);
                }
            }
        } elseif ($trap->findOid('CM-ENTITY-MIB::ne')) {
            if ($trap->findOid('CM-ENTITY-MIB::neName')) {
                $neName = $trap->getOidData($trap->findOid("CM-ENTITY-MIB::neName"));
                log_event("Network Element name changed to $neName.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-ENTITY-MIB::neCmdPromptPrefix')) {
                $neCLI = $trap->getOidData($trap->findOid('CM-ENTITY-MIB::neCmdPromptPrefix'));
                log_event("Network Element prompt changed to $neCLI.", $device_array, 'trap', 2);
            }
        } elseif ($trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled')) {
            $nteSDGEnable = $trap->getOidData($trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled'));
            if ('true' === $nteSDGEnable && $trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled')) {
                log_event("SNMP Dying Gasp is enabled.", $device_array, 'trap', 2);
            } elseif ('false' === $nteSDGEnable && $trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled')) {
                log_event("SNMP Dying Gasp is disabled.", $device_array, 'trap', 2);
            }
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPort')) {
            $netPort = substr($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPort'), -7);
            $netPort = str_replace(".", "-", $netPort);
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed')) {
                $netSpeed = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed'));
                log_event("Network Port $netPort changed speed to $netSpeed.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMediaType')) {
                $netMedia = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMediaType'));
                log_event("Network Port $netPort changed media to $netMedia.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMDIXType')) {
                $netMDIX = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMDIXType'));
                log_event("Network Port $netPort changed MDIX to $netMDIX.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled')) {
                $netAutoDiag = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled'));
                if ('true' === $netAutoDiag && $trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled')) {
                    log_event("Network Port $netPort AutoDiagnostic enabled.", $device_array, 'trap', 2);
                } elseif ('false' === $netAutoDiag && $trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled')) {
                    log_event("Network Port $netPort AutoDiagnostic disabled.", $device_array, 'trap', 2);
                }
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAdminState')) {
                $netAdminState = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAdminState'));
                log_event("Network Port $netPort administrative state changed to $netAdminState.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMTU')) {
                $netMTU = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMTU'));
                log_event("Network Port $netPort MTU changed to $netMTU bytes.", $device_array, 'trap', 2);
            } else {
                /* Catch all other Access Port changes and give a generic message */
                log_event("Network Port $netPort modified", $device_array, 'trap', 2);
            }
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPort')) {
            $accPort = substr($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPort'), -7);
            $accPort = str_replace(".", "-", $accPort);
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortConfigSpeed')) {
                $accSpeed = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortConfigSpeed'));
                log_event("Access Port $accPort changed speed to $accSpeed.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMediaType')) {
                $accMedia = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMediaType'));
                log_event("Access Port $accPort changed media to $accMedia.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMDIXType')) {
                $accMDIX = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMDIXType'));
                log_event("Access Port $accPort changed MDIX to $accMDIX.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEtheraccAccPortAutoDiagEnabled')) {
                $accAutoDiag = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAutoDiagEnabled'));
                if ('true' === $accAutoDiag && $trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAutoDiagEnabled')) {
                    log_event("Access Port $accPort AutoDiagnostic enabled.", $device_array, 'trap', 2);
                } elseif ('false' === $accAutoDiag && $trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAutoDiagEnabled')) {
                    log_event("Access Port $accPort AutoDiagnostic disabled.", $device_array, 'trap', 2);
                }
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAdminState')) {
                $accAdminState = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAdminState'));
                log_event("Access Port $accPort administrative state changed to $accAdminState.", $device_array, 'trap', 2);
            }
            if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMTU')) {
                $accMTU = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMTU'));
                log_event("Access Port $accPort MTU changed to $accMTU bytes.", $device_array, 'trap', 2);
            } else {
                /* Catch all other Access Port changes and give a generic message */
                log_event("Access Port $accPort modified", $device_array, 'trap', 2);
            }
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmFlow')) {
            $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmFlow'), -9);
            $flowID = str_replace(".", "-", $flowID);
            log_event("Access Flow $flowID modified.", $device_array, 'trap', 2);
        } elseif ($trap->findOid('F3-LAG-MIB')) {
            $lagID = substr($trap->findOid('F3-LAG-MIB::f3'), -1);
            log_event("LAG $lagID modified.", $device_array, 'trap', 2);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmQosFlow')) {
            $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmQosFlow'), -13, 9);
            $flowID = str_replace(".", "-", $flowID);
            log_event("QoS on flow $flowID modified.", $device_array, 'trap', 2);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmQosShaper')) {
            $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmQosShaper'), -13, 9);
            $flowID = str_replace(".", "-", $flowID);
            log_event("QoS on flow $flowID modified.", $device_array, 'trap', 2);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmAccPort')) {
            $shaperID = substr($trap->findOid('CM-FACILITY-MIB::cmAccPort'), -9);
            $shaperID = str_replace(".", "-", $shaperID);
            log_event("Shaper modified on access port $shaperID modified.", $device_array, 'trap', 2);
        }
    }
}
