<?php
/**
 * SnmpQueryOptions.php
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

use Illuminate\Support\Arr;

class SnmpQueryOptions
{
    public function __construct(
        public string $context = '',
        public array $mibs = ['SNMPv2-TC', 'SNMPv2-MIB', 'IF-MIB', 'IP-MIB', 'TCP-MIB', 'UDP-MIB', 'NET-SNMP-VACM-MIB'],
        public array $mibDirs = [],

        // Query behavior (Note: tolerateUnorderedIndexes and bulk are resolved per OID chunk, not request-wide constants)
        public bool $tolerateUnorderedIndexes = false,
        public bool $bulk = true,

        // Output formatting
        public bool $outputOidsNumerically = false,
        public bool $outputIndexesNumerically = false,
        public bool $outputMibNames = true,
        public bool $outputEnumsAsStrings = false,
    ) {}

    public function parseCli(array|string|null $flags): self
    {
        if ($flags === null) {
            $this->context = '';
            $this->outputOidsNumerically = false;
            $this->outputIndexesNumerically = false;
            $this->outputMibNames = true;
            $this->outputEnumsAsStrings = false;
            $this->tolerateUnorderedIndexes = false;
            $this->bulk = true;

            return $this;
        }

        foreach (Arr::wrap($flags) as $flag) {
            if (! is_string($flag)) {
                continue;
            }

            if (str_starts_with($flag, '-O')) {
                $opts = substr($flag, 2);
                if (str_contains($opts, 'n')) {
                    $this->outputOidsNumerically = true;
                }
                if (str_contains($opts, 'b')) {
                    $this->outputIndexesNumerically = true;
                }
                if (str_contains($opts, 's')) {
                    $this->outputMibNames = false;
                }
                if (str_contains($opts, 'S')) {
                    $this->outputMibNames = true;
                }
                if (str_contains($opts, 'e')) {
                    $this->outputEnumsAsStrings = false;
                } else {
                    $this->outputEnumsAsStrings = true;
                }
            } elseif (str_starts_with($flag, '-C')) {
                $opts = substr($flag, 2);
                if (str_contains($opts, 'c') || str_contains($opts, 'i')) {
                    $this->tolerateUnorderedIndexes = true;
                }
            }
        }

        return $this;
    }
}
