<?php

/**
 * XklAutomaticOTDRTriggered.php
 *
 * -Description-
 *
 * XKL Automatic OTDR was run, indicating a disruption in the fiber at
 * the length provided.
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
 * @link       http://librenms.org
 *
 * @copyright  2026 Heath Barnhart
 * @author     Heath Barnhart hbarnhart@kanren.net
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class XklAutomaticOTDRTriggered implements SnmptrapHandler
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
        $otdrDescr = $trap->getOidData($trap->findOid('XKL-MIB::xklOTDRLatestMeasurementOSCDescr'));
        $otdrDistance = $trap->getOidData($trap->findOid('XKL-MIB::xklOTDRLatestMeasurementResults'));
        $otdrDate = $trap->getOidData($trap->findOid('XKL-MIB::xklOTDRLatestMeasurementDateTime'));

        $message = "Automatic OTDR Event at $otdrDate. Name: $otdrDescr Length: $otdrDistance";

        $trap->log($message, Severity::Warning);
    }
}
