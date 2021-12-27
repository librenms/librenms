<?php
/*
 * SnmpQueryInterface.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source;

use App\Models\Device;

interface SnmpQueryInterface
{
    /**
     * Easy way to start a new instance
     */
    public static function make(): SnmpQueryInterface;

    /**
     * Specify a device to make the snmp query against.
     * By default the query will use the primary device.
     */
    public function device(Device $device): SnmpQueryInterface;

    /**
     * Specify a device by a device array.
     * The device will be fetched from the cache if it is loaded, otherwise, it will fill the array into a new Device
     */
    public function deviceArray(array $device): SnmpQueryInterface;

    /**
     * Set a context for the snmp query
     * This is most commonly used to fetch alternate sets of data, such as different VRFs
     */
    public function context(string $context): SnmpQueryInterface;

    /**
     * Set an additional MIB directory to search for MIBs.
     * You do not need to specify the base and os directories, they are already included.
     */
    public function mibDir(?string $dir): SnmpQueryInterface;

    /**
     * Do not error on out of order indexes.
     * Use with caution as we could get stuck in an infinite loop.
     */
    public function allowUnordered(): SnmpQueryInterface;

    /**
     * Output all OIDs numerically
     */
    public function numeric(): SnmpQueryInterface;

    /**
     * Hide MIB in output
     */
    public function hideMib(): SnmpQueryInterface;

    /**
     * Output enum values as strings instead of values. This could affect index output.
     */
    public function enumStrings(): SnmpQueryInterface;

    /**
     * Set option(s) for net-snmp command line.
     * Some options may break parsing, but you can manually parse the raw output if needed.
     * This will override other options set such as setting numeric.  Call with no options to reset to default.
     * Try to avoid setting options this way to keep the API generic.
     *
     * @param  array|string|null  $options
     * @return $this
     */
    public function options($options = []): SnmpQueryInterface;

    /**
     * snmpget an OID
     * Commonly used to fetch a single or multiple explicit values.
     *
     * @param  array|string  $oid
     * @return \LibreNMS\Data\Source\SnmpResponse
     */
    public function get($oid): SnmpResponse;

    /**
     * snmpwalk an OID
     * Fetches all OIDs under a given OID, commonly used with tables.
     *
     * @param  array|string  $oid
     * @return \LibreNMS\Data\Source\SnmpResponse
     */
    public function walk($oid): SnmpResponse;

    /**
     * snmpnext for the given oid
     * snmpnext retrieves the first oid after the given oid.
     *
     * @param  array|string  $oid
     * @return \LibreNMS\Data\Source\SnmpResponse
     */
    public function next($oid): SnmpResponse;

    /**
     * Translate an OID.
     * Call numeric method prior output numeric OID.
     */
    public function translate(string $oid, ?string $mib = null): SnmpResponse;
}
