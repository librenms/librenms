<?php
/**
 * PANTrap.php
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
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

/**
 * Log events to the event table
 *
 * @param string $text message describing the event
 * @param Device $device related device
 * @param string $type brief category for this event. Examples: sensor, state, stp, system, temperature, interface
 * @param int $severity 1: ok, 2: info, 3: notice, 4: warning, 5: critical, 0: unknown
 * @param int $reference the id of the referenced entity.  Supported types: interface
 */
 
class PANTrap implements SnmptrapHandler
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
        /**
         * To avoid having to create a bucket load of classes we handle
         * similar traps in a single super class
         */
        
        /**
         * Start by extracting the common fields from the trap
         */
        $trapName = $trap->getTrapOid();
        $trapDescription = $trap->getOidData($trap->findOid('PAN-TRAPS::panSystemDescription'));
        $trapReceiveTime = $trap->getOidData($trap->findOid('PAN-TRAPS::panReceiveTime'));
        $trapSerial = $trap->getOidData($trap->findOid('PAN-TRAPS::panSerial'));
        $trapEventType = $trap->getOidData($trap->findOid('PAN-TRAPS::panEventType'));
        $trapEventSubType = $trap->getOidData($trap->findOid('PAN-TRAPS::panEventSubType'));
        $trapEventId = $trap->getOidData($trap->findOid('PAN-TRAPS::panSystemEventId'));
        $trapObject = $trap->getOidData($trap->findOid('PAN-TRAPS::panSystemObject'));
        $trapModule = $trap->getOidData($trap->findOid('PAN-TRAPS::panSystemModule'));
        $trapHostname = $trap->getOidData($trap->findOid('PAN-TRAPS::panHostname'));
        $trapSeverity = $trap->getOidData($trap->findOid('PAN-TRAPS::panSystemSeverity'));
        if (is_numeric($trapSeverity)) {
            switch ($trapSeverity) {
                case 1:
                    $trapSeverity = 'Informational';
                    break;
                case 2:
                    $trapSeverity = 'Low';
                    break;
                case 3:
                    $trapSeverity = 'Medium';
                    break;
                case 4:
                    $trapSeverity = 'High';
                    break;
                case 5:
                    $trapSeverity = 'Critical';
                    break;
                default:
                    $trapSeverity = 'Unused';
                    break;
            }
        }
        $eventLevel = 0;
        switch (strtolower($trapSeverity)) {
            case "informational":
                $eventLevel = 2;
                break;
            case "low":
                $eventLevel = 3;
                break;
            case "medium":
                $eventLevel = 4;
                break;
            case "high":
                $eventLevel = 5;
                break;
            case "critical":
                $eventLevel = 5;
                break;
        }
        
        /**
         * Handle recovery messages by changing the event level to OK
         */
        switch ($trapName) {
            case "PAN-TRAPS::panHAPathMonitorUpTrap":
            case "PAN-TRAPS::panHALinkMonitorUpTrap":
            case "PAN-TRAPS::panHAPeerVersionSupportedTrap":
            case "PAN-TRAPS::panPBFNhUpTrap":
            case "PAN-TRAPS::panROUTINGRoutedDaemonStartTrap":
            case "PAN-TRAPS::panROUTINGRoutedBGPPeerEnterEstablishedTrap":
            case "PAN-TRAPS::panVPNTunnelStatusUpTrap":
            case "PAN-TRAPS::panVMDvfInitSucceedTrap":
            case "PAN-TRAPS::panGRETunnelStatusUpTrap":
            case "PAN-TRAPS::panROUTINGRoutedOSPFNeighborFullTrap":
            case "PAN-TRAPS::panROUTINGRoutedOSPFStoppedGracefulRestartTrap":
                $eventLevel = 0; // ok
                break;
        }
        
        /**
         * Attempt to map this event to a device based on the serial number, as all traps will be received from Panorama.
         */
        if (empty($trapSerial)) {
            Log::warning("Snmptrap $trapName: Had an empty panSerial value.");
            Log::event($trap->getRaw(), $device->device_id, 'trap', $eventLevel);
            return;
        }
        $realDevice = Device::where('serial', $trapSerial)->first();
        if (empty($realDevice)) {
            Log::warning("Snmptrap $trapName: Could not find device with serial number $portSerial");
            $realDevice = $device;
        }
        
        /**
         * Enrich traps
         */
        $targetPort = "";
        if (preg_match('/interface (ethernet[0-9]\/[0-9\.]+)/i', $trapDescription, $matches) !== false) {
            //Log::warning("Looking for interface " . $matches[1]);
            $targetPort = $realDevice->ports()->where('ifName', strtolower($matches[1]))->first();
        }
        
        /**
         * For now we just log the event.
         */
        if (!empty($targetPort)) {
            Log::event("$trapDescription", $realDevice->device_id, 'interface', $eventLevel, $targetPort->port_id);
        } else {
            Log::event("$trapDescription", $realDevice->device_id, "trap", $eventLevel);
        }
        // Log::event("$trapName|$trapEventType|$trapEventSubType|$trapEventId|$trapObject|$trapModule", $realDevice->device_id, "debug", 0);
    }
}
