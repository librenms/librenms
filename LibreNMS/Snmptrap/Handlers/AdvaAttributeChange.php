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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Attribute change traps show changes to Adva configuration values after they are submitted.
 * This handler only catches some of those changes and aims to provide the user with
 * information about what configuration module was modified.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

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
        if ($trap->findOid('CM-SYSTEM-MIB::sysLog')) {
            $this->handleSyslogChg($device, $trap);
        } elseif ($trap->findOid('CM-SYSTEM-MIB::aclEntry')) {
            $this->handleAclChg($device, $trap);
        } elseif ($trap->findOid('CM-SYSTEM-MIB::securityBanner')) {
            Log::event('MOTD/Banner modified', $device->device_id, 'trap', 2);
        } elseif ($trap->findOid('CM-SYSTEM-MIB::sysTimeOfDayType')) {
            $this->handleTimeSrcChg($device, $trap);
        } elseif ($trap->findOid('F3-TIMEZONE-MIB::f3TimeZone')) {
            $this->handleTimeZoneChg($device, $trap);
        } elseif ($trap->findOid('CM-SYSTEM-MIB::ntp')) {
            $this->handleNtpChg($device, $trap);
        } elseif ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServer')) {
            $this->handleAuthSvrChg($device, $trap);
        } elseif ($trap->findOid('CM-ENTITY-MIB::ne')) {
            $this->handleNeChg($device, $trap);
        } elseif ($trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled')) {
            $this->handleDyingGaspChg($device, $trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPort')) {
            $this->handleNetPortChg($device, $trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPort')) {
            $this->handleAccPortChg($device, $trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmFlow')) {
            $this->handleFlowChg($device, $trap);
        } elseif ($trap->findOid('F3-LAG-MIB')) {
            $this->handleLagChg($device, $trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmQosFlow')) {
            $this->handleQosFlowChg($device, $trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmQosShaper')) {
            $this->handleQosShaperChg($device, $trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmAccPort')) {
            $this->handleAccPortShaperChg($device, $trap);
        }
    }

    public static function handleSyslogChg($device, $trap)
    {
        $syslogEntry = substr($trap->findOid('CM-SYSTEM-MIB::sysLog'), -1);
        if ($trap->findOid('CM-SYSTEM-MIB::sysLogIpVersion')) {
            $ipVer = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogIpVersion'));
            Log::event("Syslog server $syslogEntry IP version set to $ipVer", $device->device_id, 'trap', 2);
        }
        if ($trap->findOid('CM-SYSTEM-MIB::sysLogIpAddress')) {
            $ipAddr = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogIpAddress'));
            Log::event("Syslog server $syslogEntry IP address changed to $ipAddr", $device->device_id, 'trap', 2);
        }
        if ($trap->findOid('CM-SYSTEM-MIB::sysLogIpv6Addr')) {
            $ip6Addr = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogIpv6Addr'));
            Log::event("Syslog server $syslogEntry IP address changed to $ip6Addr", $device->device_id, 'trap', 2);
        }
        if ($trap->findOid('CM-SYSTEM-MIB::sysLogPort')) {
            $syslogPort = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogPort'));
            Log::event("Syslog server $syslogEntry port changed to $syslogPort", $device->device_id, 'trap', 2);
        }
    }

    public static function handleAclChg($device, $trap)
    {
        $aclEntry = substr($trap->findOid('CM-SYSTEM-MIB::aclEntry'), -1);
        Log::event("ACL $aclEntry modified", $device->device_id, 'trap', 2);
    }

    public static function handleTimeSrcChg($device, $trap)
    {
        $timeSrc = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysTimeOfDayType'));
        Log::event("Time source set to $timeSrc", $device->device_id, 'trap', 2);
    }

    public static function handleTimeZoneChg($device, $trap)
    {
        $enabled = $trap->getOidData($trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled'));
        if ('true' === $enabled && $trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled')) {
            Log::event('Daylight Savings Time enabled', $device->device_id, 'trap', 2);
        } elseif ('false' === $enabled && $trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled')) {
            Log::event('Daylight Savings Time disabled', $device->device_id, 'trap', 2);
        }
        if ($trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneUtcOffset')) {
            $dstOffset = $trap->getOidData($trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneUtcOffset'));
            Log::event("UTC offset (timezone) change to $dstOffset", $device->device_id, 'trap', 2);
        }
    }

    public static function handleNtpChg($device, $trap)
    {
        if ($trap->findOid('CM-SYSTEM-MIB::ntpPrimaryServer')) {
            $primaryIP = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::ntpPrimaryServer'));
            Log::event("Primary NTP server IP changed to $primaryIP", $device->device_id, 'trap', 2);
        }
        if ($trap->findOid('CM-SYSTEM-MIB::ntpBackupServer')) {
            $backupIP = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::ntpBackupServer'));
            Log::event("Backup NTP server IP changed to $backupIP", $device->device_id, 'trap', 2);
        }
    }

    public static function handleAuthSvrChg($device, $trap)
    {
        if ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerIpAddress')) {
            $serverEntry = substr($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerIpAddress'), -1);
            $serverIP = $trap->getOidData($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerIpAddress'));
            Log::event("Authentication server $serverEntry IP changed to $serverIP", $device->device_id, 'trap', 2);
        }
        if ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerSecret')) {
            $serverEntry = substr($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerSecret'), -1);
            Log::event("Authentication server $serverEntry secret changed", $device->device_id, 'trap', 2);
        }
        if ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerEnabled')) {
            $serverEntry = substr($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerEnabled'), -1);
            $serverEnable = $trap->getOidData($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerEnabled'));
            if ('true' === $serverEnable) {
                Log::event("Authentication server $serverEntry enabled", $device->device_id, 'trap', 2);
            } else {
                Log::event("Authentication server $serverEntry disabled", $device->device_id, 'trap', 2);
            }
        }
    }

    public static function handleNeChg($device, $trap)
    {
        if ($trap->findOid('CM-ENTITY-MIB::neName')) {
            $neName = $trap->getOidData($trap->findOid('CM-ENTITY-MIB::neName'));
            Log::event("Network Element name changed to $neName", $device->device_id, 'trap', 2);
        }
        if ($trap->findOid('CM-ENTITY-MIB::neCmdPromptPrefix')) {
            $neCLI = $trap->getOidData($trap->findOid('CM-ENTITY-MIB::neCmdPromptPrefix'));
            Log::event("Network Element prompt changed to $neCLI", $device->device_id, 'trap', 2);
        }
    }

    public static function handleDyingGaspChg($device, $trap)
    {
        $nteSDGEnable = $trap->getOidData($trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled'));
        if ('true' === $nteSDGEnable && $trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled')) {
            Log::event('SNMP Dying Gasp is enabled', $device->device_id, 'trap', 2);
        } elseif ('false' === $nteSDGEnable && $trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled')) {
            Log::event('SNMP Dying Gasp is disabled', $device->device_id, 'trap', 2);
        }
    }

    public static function handleNetPortChg($device, $trap)
    {
        $netPort = substr($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPort'), -7);
        $netPort = str_replace('.', '-', $netPort);
        $neDefMessage = false;
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed')) {
            $netSpeed = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed'));
            Log::event("Network Port $netPort changed speed to $netSpeed", $device->device_id, 'trap', 2);
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMediaType')) {
            $netMedia = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMediaType'));
            Log::event("Network Port $netPort changed media to $netMedia", $device->device_id, 'trap', 2);
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMDIXType')) {
            $netMDIX = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMDIXType'));
            Log::event("Network Port $netPort changed MDIX to $netMDIX", $device->device_id, 'trap', 2);
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled')) {
            $netAutoDiag = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled'));
            if ('true' === $netAutoDiag) {
                $message = "Network Port $netPort AutoDiagnostic enabled";
            } else {
                $message = "Network Port $netPort AutoDiagnostic disabled";
            }
            Log::event($message, $device->device_id, 'trap', 2);
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAdminState')) {
            $netAdminState = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAdminState'));
            Log::event("Network Port $netPort administrative state changed to $netAdminState", $device->device_id, 'trap', 2);
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMTU')) {
            $netMTU = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMTU'));
            Log::event("Network Port $netPort MTU changed to $netMTU bytes", $device->device_id, 'trap', 2);
            $neDefMessage = true;
        }
        if ($neDefMessage === false) {
            /* Catch all other Access Port changes and give a generic message */
            Log::event("Network Port $netPort modified", $device->device_id, 'trap', 2);
        }
    }

    public static function handleAccPortChg($device, $trap)
    {
        $accPort = substr($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPort'), -7);
        $accPort = str_replace('.', '-', $accPort);
        $accDefMessage = false;
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortConfigSpeed')) {
            $accSpeed = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortConfigSpeed'));
            Log::event("Access Port $accPort changed speed to $accSpeed", $device->device_id, 'trap', 2);
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMediaType')) {
            $accMedia = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMediaType'));
            Log::event("Access Port $accPort changed media to $accMedia", $device->device_id, 'trap', 2);
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMDIXType')) {
            $accMDIX = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMDIXType'));
            Log::event("Access Port $accPort changed MDIX to $accMDIX", $device->device_id, 'trap', 2);
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAutoDiagEnabled')) {
            $accAutoDiag = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAutoDiagEnabled'));
            if ('true' === $accAutoDiag) {
                $message = "Access Port $accPort AutoDiagnostic enabled";
            } else {
                $message = "Access Port $accPort AutoDiagnostic disabled";
            }
            Log::event($message, $device->device_id, 'trap', 2);
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAdminState')) {
            $accAdminState = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAdminState'));
            Log::event("Access Port $accPort administrative state changed to $accAdminState", $device->device_id, 'trap', 2);
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMTU')) {
            $accMTU = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMTU'));
            Log::event("Access Port $accPort MTU changed to $accMTU bytes", $device->device_id, 'trap', 2);
            $accDefMessage = true;
        }
        if ($accDefMessage === false) {
            /* Catch all other Access Port changes and give a generic message */
            Log::event("Access Port $accPort modified", $device->device_id, 'trap', 2);
        }
    }

    public static function handleFlowChg($device, $trap)
    {
        $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmFlow'), -9);
        $flowID = str_replace('.', '-', $flowID);
        Log::event("Access Flow $flowID modified", $device->device_id, 'trap', 2);
    }

    public static function handleLagChg($device, $trap)
    {
        $lagID = substr($trap->findOid('F3-LAG-MIB::f3'), -1);
        Log::event("LAG $lagID modified", $device->device_id, 'trap', 2);
    }

    public static function handleQosFlowChg($device, $trap)
    {
        $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmQosFlow'), -13, 9);
        $flowID = str_replace('.', '-', $flowID);
        Log::event("QoS on flow $flowID modified", $device->device_id, 'trap', 2);
    }

    public static function handleQosShaperChg($device, $trap)
    {
        $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmQosShaper'), -13, 9);
        $flowID = str_replace('.', '-', $flowID);
        Log::event("QoS on flow $flowID modified", $device->device_id, 'trap', 2);
    }

    public static function handleAccPortShaperChg($device, $trap)
    {
        $shaperID = substr($trap->findOid('CM-FACILITY-MIB::cmAccPort'), -9);
        $shaperID = str_replace('.', '-', $shaperID);
        Log::event("Shaper modified on access port $shaperID modified", $device->device_id, 'trap', 2);
    }
}
