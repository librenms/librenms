<?php

/**
 * SnmpQueryOptions.php
 *
 * Configuration options controlling SNMP command arguments, OID formats, and output presentation.
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

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;

class SnmpQueryOptions
{
    /**
     * @param  string[]  $mibs
     * @param  string[]  $mibDirs
     */
    public function __construct(
        public string $context = '',
        public array $mibs = ['SNMPv2-TC', 'SNMPv2-MIB', 'IF-MIB', 'IP-MIB', 'TCP-MIB', 'UDP-MIB', 'NET-SNMP-VACM-MIB'],
        public array $mibDirs = [],

        // Query behavior
        public bool $allowBulk = true,
        public bool $tolerateUnorderedIndexes = false,

        // OID formatting
        public bool $numericIndexes = false,
        public SnmpOidOutput $oidFormat = SnmpOidOutput::Module,

        // Value formatting
        public bool $escapeQuotes = false,
        public bool $numericEnums = false,
        public bool $numericTimeticks = false,
        public bool $printHexText = false,
        public bool $printUnits = true,
        public bool $applyDisplayHints = true,
        public SnmpStringOutput $stringFormat = SnmpStringOutput::Guess,

        // Output presentation
        public bool $quickPrint = false,
        public bool $extendedIndex = false,
        public bool $allowUnderscores = false,
    ) {
    }

    public static function quickPrint(): self
    {
        $options = new self;

        $options->numericEnums = true;
        $options->numericTimeticks = true;
        $options->printUnits = false;
        $options->quickPrint = true;
        $options->extendedIndex = true;

        return $options;
    }

    public function createPerWalkInstance(string $os, string $oid): self
    {
        $options = clone $this;

        if (in_array($oid, LibrenmsConfig::getCombined($os, 'oids.unordered', 'snmp.'))) {
            $options->tolerateUnorderedIndexes = true;
        }

        if (in_array($oid, LibrenmsConfig::getCombined($os, 'oids.no_bulk', 'snmp.'))) {
            $options->allowBulk = false;
        }

        return $options;
    }
}
