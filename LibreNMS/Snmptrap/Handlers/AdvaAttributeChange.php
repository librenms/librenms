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
 *
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
     * @param  Device  $device
     * @param  Trap  $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap): void
    {
        if ($trap->findOid('CM-SYSTEM-MIB::sysLog')) {
            $this->handleSyslogChg($trap);
        } elseif ($trap->findOid('CM-SYSTEM-MIB::aclEntry')) {
            $this->handleAclChg($trap);
        } elseif ($trap->findOid('CM-SYSTEM-MIB::securityBanner')) {
            $trap->log('MOTD/Banner modified');
        } elseif ($trap->findOid('CM-SYSTEM-MIB::sysTimeOfDayType')) {
            $this->handleTimeSrcChg($trap);
        } elseif ($trap->findOid('F3-TIMEZONE-MIB::f3TimeZone')) {
            $this->handleTimeZoneChg($trap);
        } elseif ($trap->findOid('CM-SYSTEM-MIB::ntp')) {
            $this->handleNtpChg($trap);
        } elseif ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServer')) {
            $this->handleAuthSvrChg($trap);
        } elseif ($trap->findOid('CM-ENTITY-MIB::ne')) {
            $this->handleNeChg($trap);
        } elseif ($trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled')) {
            $this->handleDyingGaspChg($trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPort')) {
            $this->handleNetPortChg($trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPort')) {
            $this->handleAccPortChg($trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmFlow')) {
            $this->handleFlowChg($trap);
        } elseif ($trap->findOid('F3-LAG-MIB')) {
            $this->handleLagChg($trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmQosFlow')) {
            $this->handleQosFlowChg($trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmQosShaper')) {
            $this->handleQosShaperChg($trap);
        } elseif ($trap->findOid('CM-FACILITY-MIB::cmAccPort')) {
            $this->handleAccPortShaperChg($trap);
        }
    }

    public function handleSyslogChg(Trap $trap): void
    {
        $syslogEntry = substr($trap->findOid('CM-SYSTEM-MIB::sysLog'), -1);
        if ($trap->findOid('CM-SYSTEM-MIB::sysLogIpVersion')) {
            $ipVer = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogIpVersion'));
            $trap->log("Syslog server $syslogEntry IP version set to $ipVer");
        }
        if ($trap->findOid('CM-SYSTEM-MIB::sysLogIpAddress')) {
            $ipAddr = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogIpAddress'));
            $trap->log("Syslog server $syslogEntry IP address changed to $ipAddr");
        }
        if ($trap->findOid('CM-SYSTEM-MIB::sysLogIpv6Addr')) {
            $ip6Addr = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogIpv6Addr'));
            $trap->log("Syslog server $syslogEntry IP address changed to $ip6Addr");
        }
        if ($trap->findOid('CM-SYSTEM-MIB::sysLogPort')) {
            $syslogPort = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysLogPort'));
            $trap->log("Syslog server $syslogEntry port changed to $syslogPort");
        }
    }

    public function handleAclChg(Trap $trap): void
    {
        $aclEntry = substr($trap->findOid('CM-SYSTEM-MIB::aclEntry'), -1);
        $trap->log("ACL $aclEntry modified");
    }

    public function handleTimeSrcChg(Trap $trap): void
    {
        $timeSrc = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::sysTimeOfDayType'));
        $trap->log("Time source set to $timeSrc");
    }

    public function handleTimeZoneChg(Trap $trap): void
    {
        $enabled = $trap->getOidData($trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled'));
        if ('true' === $enabled && $trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled')) {
            $trap->log('Daylight Savings Time enabled');
        } elseif ('false' === $enabled && $trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled')) {
            $trap->log('Daylight Savings Time disabled');
        }
        if ($trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneUtcOffset')) {
            $dstOffset = $trap->getOidData($trap->findOid('F3-TIMEZONE-MIB::f3TimeZoneUtcOffset'));
            $trap->log("UTC offset (timezone) change to $dstOffset");
        }
    }

    public function handleNtpChg(Trap $trap): void
    {
        if ($trap->findOid('CM-SYSTEM-MIB::ntpPrimaryServer')) {
            $primaryIP = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::ntpPrimaryServer'));
            $trap->log("Primary NTP server IP changed to $primaryIP");
        }
        if ($trap->findOid('CM-SYSTEM-MIB::ntpBackupServer')) {
            $backupIP = $trap->getOidData($trap->findOid('CM-SYSTEM-MIB::ntpBackupServer'));
            $trap->log("Backup NTP server IP changed to $backupIP");
        }
    }

    public function handleAuthSvrChg(Trap $trap): void
    {
        if ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerIpAddress')) {
            $serverEntry = substr($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerIpAddress'), -1);
            $serverIP = $trap->getOidData($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerIpAddress'));
            $trap->log("Authentication server $serverEntry IP changed to $serverIP");
        }
        if ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerSecret')) {
            $serverEntry = substr($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerSecret'), -1);
            $trap->log("Authentication server $serverEntry secret changed");
        }
        if ($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerEnabled')) {
            $serverEntry = substr($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerEnabled'), -1);
            $serverEnable = $trap->getOidData($trap->findOid('CM-SECURITY-MIB::cmRemoteAuthServerEnabled'));
            if ('true' === $serverEnable) {
                $trap->log("Authentication server $serverEntry enabled");
            } else {
                $trap->log("Authentication server $serverEntry disabled");
            }
        }
    }

    public function handleNeChg(Trap $trap): void
    {
        if ($trap->findOid('CM-ENTITY-MIB::neName')) {
            $neName = $trap->getOidData($trap->findOid('CM-ENTITY-MIB::neName'));
            $trap->log("Network Element name changed to $neName");
        }
        if ($trap->findOid('CM-ENTITY-MIB::neCmdPromptPrefix')) {
            $neCLI = $trap->getOidData($trap->findOid('CM-ENTITY-MIB::neCmdPromptPrefix'));
            $trap->log("Network Element prompt changed to $neCLI");
        }
    }

    public function handleDyingGaspChg(Trap $trap): void
    {
        $nteSDGEnable = $trap->getOidData($trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled'));
        if ('true' === $nteSDGEnable && $trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled')) {
            $trap->log('SNMP Dying Gasp is enabled');
        } elseif ('false' === $nteSDGEnable && $trap->findOid('CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled')) {
            $trap->log('SNMP Dying Gasp is disabled');
        }
    }

    public function handleNetPortChg(Trap $trap): void
    {
        $netPort = substr($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPort'), -7);
        $netPort = str_replace('.', '-', $netPort);
        $neDefMessage = false;
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed')) {
            $netSpeed = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed'));
            $trap->log("Network Port $netPort changed speed to $netSpeed");
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMediaType')) {
            $netMedia = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMediaType'));
            $trap->log("Network Port $netPort changed media to $netMedia");
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMDIXType')) {
            $netMDIX = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMDIXType'));
            $trap->log("Network Port $netPort changed MDIX to $netMDIX");
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled')) {
            $netAutoDiag = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled'));
            if ('true' === $netAutoDiag) {
                $message = "Network Port $netPort AutoDiagnostic enabled";
            } else {
                $message = "Network Port $netPort AutoDiagnostic disabled";
            }
            $trap->log($message);
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAdminState')) {
            $netAdminState = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAdminState'));
            $trap->log("Network Port $netPort administrative state changed to $netAdminState");
            $neDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMTU')) {
            $netMTU = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortMTU'));
            $trap->log("Network Port $netPort MTU changed to $netMTU bytes");
            $neDefMessage = true;
        }
        if ($neDefMessage === false) {
            /* Catch all other Access Port changes and give a generic message */
            $trap->log("Network Port $netPort modified");
        }
    }

    public function handleAccPortChg(Trap $trap): void
    {
        $accPort = substr($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPort'), -7);
        $accPort = str_replace('.', '-', $accPort);
        $accDefMessage = false;
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortConfigSpeed')) {
            $accSpeed = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortConfigSpeed'));
            $trap->log("Access Port $accPort changed speed to $accSpeed");
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMediaType')) {
            $accMedia = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMediaType'));
            $trap->log("Access Port $accPort changed media to $accMedia");
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMDIXType')) {
            $accMDIX = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMDIXType'));
            $trap->log("Access Port $accPort changed MDIX to $accMDIX");
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAutoDiagEnabled')) {
            $accAutoDiag = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAutoDiagEnabled'));
            if ('true' === $accAutoDiag) {
                $message = "Access Port $accPort AutoDiagnostic enabled";
            } else {
                $message = "Access Port $accPort AutoDiagnostic disabled";
            }
            $trap->log($message);
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAdminState')) {
            $accAdminState = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAdminState'));
            $trap->log("Access Port $accPort administrative state changed to $accAdminState");
            $accDefMessage = true;
        }
        if ($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMTU')) {
            $accMTU = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortMTU'));
            $trap->log("Access Port $accPort MTU changed to $accMTU bytes");
            $accDefMessage = true;
        }
        if ($accDefMessage === false) {
            /* Catch all other Access Port changes and give a generic message */
            $trap->log("Access Port $accPort modified");
        }
    }

    public function handleFlowChg(Trap $trap): void
    {
        $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmFlow'), -9);
        $flowID = str_replace('.', '-', $flowID);
        $trap->log("Access Flow $flowID modified");
    }

    public function handleLagChg(Trap $trap): void
    {
        $lagID = substr($trap->findOid('F3-LAG-MIB::f3'), -1);
        $trap->log("LAG $lagID modified");
    }

    public function handleQosFlowChg(Trap $trap): void
    {
        $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmQosFlow'), -13, 9);
        $flowID = str_replace('.', '-', $flowID);
        $trap->log("QoS on flow $flowID modified");
    }

    public function handleQosShaperChg(Trap $trap): void
    {
        $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmQosShaper'), -13, 9);
        $flowID = str_replace('.', '-', $flowID);
        $trap->log("QoS on flow $flowID modified");
    }

    public function handleAccPortShaperChg(Trap $trap): void
    {
        $shaperID = substr($trap->findOid('CM-FACILITY-MIB::cmAccPort'), -9);
        $shaperID = str_replace('.', '-', $shaperID);
        $trap->log("Shaper modified on access port $shaperID modified");
    }
}
