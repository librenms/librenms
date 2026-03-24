<?php

/*
 * SnmpTranslateInterface.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Data\Source;

use App\Models\Device;

interface SnmpTranslateInterface
{
    /**
     * Easy way to start a new instance
     */
    public static function make(): SnmpTranslateInterface;

    /**
     * Specify a device to make the snmp query against.
     * By default the query will use the primary device.
     */
    public function device(Device $device): SnmpTranslateInterface;

    /**
     * Set an additional MIB directory to search for MIBs.
     * You do not need to specify the base and os directories, they are already included.
     */
    public function mibDir(?string $dir): SnmpTranslateInterface;

    /**
     * Set MIBs to use for this query. Base mibs are included by default.
     * They will be appended to existing mibs unless $append is set to false.
     */
    public function mibs(array $mibs, bool $append = true): SnmpTranslateInterface;

    /**
     * Output all OIDs numerically
     */
    public function numeric(bool $numeric = true): SnmpTranslateInterface;

    /**
     * Hide MIB in output
     */
    public function hideMib(): SnmpTranslateInterface;

    /**
     * Translate an OID.
     * Call numeric method prior output numeric OID.
     */
    public function translate(string $oid): string;
}
