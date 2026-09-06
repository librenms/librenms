<?php

/**
 * SnmpBackendInterface.php
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
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Snmp;

use LibreNMS\Data\Source\SnmpResponse;

/**
 * Executes SNMP requests against a device over the wire.
 *
 * Implementations own the transport (CLI binaries, php-snmp sessions, etc.)
 * and translate SnmpTarget/SnmpQueryOptions into whatever that transport
 * requires. Callers should depend on this interface, not a concrete
 * implementation, so the backend can be swapped without touching query logic.
 *
 * Caching, retry-on-failure looping across multiple oid groups, and oid
 * chunking are the caller's responsibility, not the backend's — each method
 * here represents exactly one request to the device.
 */
interface SnmpBackendInterface
{
    /**
     * snmpget one or more OIDs — fetches explicit values.
     * Every OID is fetched in a single request; the caller is responsible
     * for keeping $oids within the target's max-oids-per-request limit.
     *
     * @param  string[]  $oids
     */
    public function get(SnmpTarget $target, array $oids, SnmpQueryOptions $options): SnmpResponse;

    /**
     * snmpwalk (or snmpbulkwalk, per $options) a single OID subtree —
     * fetches every OID beneath it, commonly used for tables.
     *
     * Takes one OID, not an array: unlike get()/next(), a walk can't
     * usefully batch multiple subtrees into a single request, since each
     * subtree ends at a different point and streams back independently.
     */
    public function walk(SnmpTarget $target, string $oid, SnmpQueryOptions $options): SnmpResponse;

    /**
     * snmpgetnext for one or more OIDs — fetches the first OID after each
     * given OID. Same batching contract as get().
     *
     * @param  string[]  $oids
     */
    public function next(SnmpTarget $target, array $oids, SnmpQueryOptions $options): SnmpResponse;
}
