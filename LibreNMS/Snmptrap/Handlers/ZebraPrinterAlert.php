<?php

/**
 * ZebraPrinterAlert.php
 *
 * Handles ZEBRA-QL-MIB::zebra.1.0.1 traps
 * Zebra Link-OS printer alert/error condition
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
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class ZebraPrinterAlert implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param  Device  $device
     * @param  Trap  $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $message = $trap->getOidData('ESI-MIB::psOutput.7');
        $severity = $this->getSeverity($message);

        $trap->log($message, $severity, 'printer');
    }

    private function getSeverity(string $message): Severity
    {
        if (preg_match('/PAPER OUT|RIBBON OUT|CUTTER JAM|HEAD ELEMENT BAD|REPLACE HEAD|MOTOR OVERTEMP|PRINTHEAD SHUTDOWN|THERMISTOR FAULT|INVALID HEAD|MEDIA CARTRIDGE LOAD FAILURE|PAPER ERROR|RIBBON AUTH ERROR/', $message)) {
            return Severity::Error;
        }

        if (preg_match('/HEAD OPEN|HEAD TOO HOT|HEAD COLD|SUPPLY TOO HOT|MEDIA LOW|RIBBON LOW|BATTERY LOW|CLEAN PRINTHEAD|RFID ERROR|REWIND|NO READER PRESENT|BATTERY MISSING|MEDIA CARTRIDGE EJECT FAILURE|MEDIA CARTRIDGE FORCED EJECT|RIBBON TENSION|COVER OPEN|CLEAN CUTTER|DUPLICATE IP|BASIC FORCED|COUNTRY CODE ERROR/', $message)) {
            return Severity::Warning;
        }

        if (preg_match('/PRINTER PAUSED|BASIC RUNTIME|SGD SET|SHUTTING DOWN|RESTARTING|PMCU DOWNLOAD|COUNTRY CODE|MEDIA CARTRIDGE|CLEANING MODE/', $message)) {
            return Severity::Info;
        }

        if (preg_match('/PQ JOB COMPLETED|LABEL READY|POWER ON|COLD START|RIBBON IN|Druckauftr Fertg/', $message)) {
            return Severity::Ok;
        }

        return Severity::Warning;
    }
}
