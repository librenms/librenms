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

use Illuminate\Support\Arr;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\Exceptions\UnsupportedSnmpOption;

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
        return (new self())->applyQuickPrintDefaults();
    }

    /**
     * @param  string[]|string|null  $args
     *
     * @throws UnsupportedSnmpOption
     */
    public function parseCli(array|string|null $args): self
    {
        if ($args === null) {
            return $this->applyQuickPrintDefaults();
        }

        $this->resetDefaults();

        $arguments = Arr::wrap($args);

        for ($i = 0; $i < count($arguments); $i++) {
            $arg = $arguments[$i];
            if (! is_string($arg)) {
                continue;
            }

            $prefix = substr($arg, 0, 2);
            $rest = substr($arg, 2);

            if ($prefix === '-O') {
                foreach (str_split($rest) as $outopt) {
                    match ($outopt) {
                        'a' => $this->stringFormat = SnmpStringOutput::Ascii,
                        'x' => $this->stringFormat = SnmpStringOutput::Hex,
                        'f' => $this->oidFormat = SnmpOidOutput::Full,
                        's' => $this->oidFormat = SnmpOidOutput::Suffix,
                        'S' => $this->oidFormat = SnmpOidOutput::Module,
                        'u' => $this->oidFormat = SnmpOidOutput::Ucd,
                        'n' => $this->oidFormat = SnmpOidOutput::Numeric,
                        'b' => $this->numericIndexes = true,
                        'e' => $this->numericEnums = true,
                        'E' => $this->escapeQuotes = true,
                        'Q' => $this->quickPrint = true,
                        't' => $this->numericTimeticks = true,
                        'T' => $this->printHexText = true,
                        'U' => $this->printUnits = false,
                        'X' => $this->extendedIndex = true,
                        default => throw new UnsupportedSnmpOption("Unknown option -O$outopt"),
                    };
                }
            } elseif ($prefix === '-C') {
                if (str_contains($rest, 'c')) {
                    $this->tolerateUnorderedIndexes = true;
                }
            } elseif ($prefix === '-P') {
                $this->allowUnderscores = str_contains($rest, 'u');
            } elseif ($prefix === '-I') {
                if (str_contains($rest, 'h')) {
                    $this->applyDisplayHints = false;
                }
            } elseif ($prefix === '-m') {
                $mib = $rest !== '' ? $rest : $this->consumeNextArg($arguments, $i);
                $this->addMibs($mib);
            } elseif ($prefix === '-M') {
                $mibDir = $rest !== '' ? $rest : $this->consumeNextArg($arguments, $i);
                if ($mibDir !== null) {
                    $this->mibDirs = explode(':', $mibDir);
                }
            }
        }

        return $this;
    }

    public function applyQuickPrintDefaults(): self
    {
        return $this->resetDefaults(true);
    }

    private function resetDefaults(bool $quickPrint = false): self
    {
        $this->context = '';
        $this->allowBulk = true;
        $this->tolerateUnorderedIndexes = false;
        $this->escapeQuotes = false;
        $this->numericIndexes = false;
        $this->printHexText = false;
        $this->applyDisplayHints = true;
        $this->stringFormat = SnmpStringOutput::Guess;
        $this->allowUnderscores = false;
        $this->oidFormat = SnmpOidOutput::Module;

        $this->numericEnums = $quickPrint;
        $this->numericTimeticks = $quickPrint;
        $this->printUnits = ! $quickPrint;
        $this->quickPrint = $quickPrint;
        $this->extendedIndex = $quickPrint;

        return $this;
    }

    private function addMibs(?string $mib): void
    {
        if ($mib === null) {
            return;
        }

        if (str_starts_with($mib, '+')) {
            $this->mibs[] = substr($mib, 1);
            $this->mibs = array_values(array_unique($this->mibs));
        } else {
            $this->mibs = explode(':', $mib);
        }
    }

    /**
     * @param  string[]  $args
     * @param  int  $i
     * @return string|null
     */
    private function consumeNextArg(array $args, int &$i): ?string
    {
        return isset($args[$i + 1]) && is_string($args[$i + 1]) ? $args[++$i] : null;
    }
}
