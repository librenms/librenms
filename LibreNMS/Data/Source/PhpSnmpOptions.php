<?php

/*
 * PhpSnmpOptions.php
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

class PhpSnmpOptions
{
    public bool $oid_increasing_check = true;
    public bool $quick_print = true;
    public bool $enum_print = true;
    public bool $numeric_index = true;
    public bool $numeric_timeticks = true;
    public bool $extended_index = true;
    public bool $dont_print_units = true;
    public bool $escape_quotes = true;
    public bool $print_hex_text = true;
    public \Snmp\StringOutput $string_output_format = \Snmp\StringOutput::Guess; /** @phpstan-ignore class.notFound, class.notFound */
    public \Snmp\OidOutput $oid_output_format = \Snmp\OidOutput::Module; /** @phpstan-ignore class.notFound, class.notFound */

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    public function defaults(): PhpSnmpOptions
    {
        $this->oid_increasing_check = true;
        $this->quick_print = true;
        $this->enum_print = true;
        $this->numeric_index = true;
        $this->numeric_timeticks = true;
        $this->extended_index = true;
        $this->dont_print_units = true;
        $this->escape_quotes = true;
        $this->print_hex_text = true;
        $this->string_output_format = \Snmp\StringOutput::Guess; /** @phpstan-ignore class.notFound */
        $this->oid_output_format = \Snmp\OidOutput::Module; /** @phpstan-ignore class.notFound */

        return $this;
    }

    public function libraryDefaults(): PhpSnmpOptions
    {
        $this->oid_increasing_check = false;
        $this->quick_print = false;
        $this->enum_print = false;
        $this->numeric_index = false;
        $this->numeric_timeticks = false;
        $this->extended_index = false;
        $this->dont_print_units = false;
        $this->escape_quotes = false;
        $this->print_hex_text = false;
        $this->string_output_format = \Snmp\StringOutput::Guess; /** @phpstan-ignore class.notFound */
        $this->oid_output_format = \Snmp\OidOutput::Module; /** @phpstan-ignore class.notFound */

        return $this;
    }

    /**
     * Parse net-snmp command line options.
     *
     * @param  string[]|string|null  $options
     */
    public function parseOptions($options = []): PhpSnmpOptions
    {
        if (is_null($options)) {
            return $this->defaults();
        }

        if (is_string($options)) {
            $options = [$options];
        }

        // Reset all options to library defaults
        $this->libraryDefaults();

        // Parse options, returning the NetSnmp object if we come across an unknown option
        foreach ($options as $option) {
            if ($option === '-Ci') {
                $this->oid_increasing_check = false;
            } elseif ($option === '-Pu') {
                // Do nothing - we always accept underscores in MIBs
            } elseif ($option === '-Ih') {
                // Ignore input options for GET requests
            } elseif (str_starts_with((string) $option, '-O')) {
                foreach (str_split(substr((string) $option, 2)) as $outopt) {
                    switch ($outopt) {
                        case 'a':
                            $this->string_output_format = \Snmp\StringOutput::Ascii; /** @phpstan-ignore class.notFound */
                            break;
                        case 'x':
                            $this->string_output_format = \Snmp\StringOutput::Hex; /** @phpstan-ignore class.notFound */
                            break;
                        case 'f':
                            $this->oid_output_format = \Snmp\OidOutput::Full; /** @phpstan-ignore class.notFound */
                            break;
                        case 's':
                            $this->oid_output_format = \Snmp\OidOutput::Suffix; /** @phpstan-ignore class.notFound */
                            break;
                        case 'S':
                            $this->oid_output_format = \Snmp\OidOutput::Module; /** @phpstan-ignore class.notFound */
                            break;
                        case 'u':
                            $this->oid_output_format = \Snmp\OidOutput::Ucd; /** @phpstan-ignore class.notFound */
                            break;
                        case 'n':
                            $this->oid_output_format = \Snmp\OidOutput::Numeric; /** @phpstan-ignore class.notFound */
                            break;
                        case 'b':
                            $this->numeric_index = true;
                            break;
                        case 'e':
                            $this->enum_print = true;
                            break;
                        case 'E':
                            $this->escape_quotes = true;
                            break;
                        case 'Q':
                            $this->quick_print = true;
                            break;
                        case 't':
                            $this->numeric_timeticks = true;
                            break;
                        case 'T':
                            $this->print_hex_text = true;
                            break;
                        case 'U':
                            $this->dont_print_units = true;
                            break;
                        case 'X':
                            $this->extended_index = true;
                            break;
                        default:
                            throw new \Exception("Unknown option -C$outopt");
                    }
                }
            } else {
                throw new \Exception("Unknown option $option");
            }
        }

        return $this;
    }

    /**
     * Set the SNMP object to the configured options
     */
    public function setOptions(\SNMP $snmp): void
    {
        $snmp->oid_increasing_check = $this->oid_increasing_check;
        $snmp->quick_print = $this->quick_print;
        $snmp->enum_print = $this->enum_print;
        $snmp->numeric_index = $this->numeric_index; /** @phpstan-ignore property.notFound */
        $snmp->numeric_timeticks = $this->numeric_timeticks; /** @phpstan-ignore property.notFound */
        $snmp->extended_index = $this->extended_index; /** @phpstan-ignore property.notFound */
        $snmp->dont_print_units = $this->dont_print_units; /** @phpstan-ignore property.notFound */
        $snmp->escape_quotes = $this->escape_quotes; /** @phpstan-ignore property.notFound */
        $snmp->print_hex_text = $this->print_hex_text; /** @phpstan-ignore property.notFound */
        $snmp->setStringOutputFormat($this->string_output_format); /** @phpstan-ignore method.notFound */
        $snmp->setOidOutputFormat($this->oid_output_format); /** @phpstan-ignore method.notFound */
    }

    /**
     * Get NetSNMP options from PhpSnmpOptions
     */
    public function getOptionString(): string
    {
        // Always accept underscores in MIBs
        $ret = ['-Pu'];

        if (! $this->oid_increasing_check) {
            $ret[] = '-Ci';
        }

        $outputOptions = '';
        $outputOptions .= match ($this->string_output_format) {
            \Snmp\StringOutput::Ascii => 'a', /** @phpstan-ignore class.notFound */
            \Snmp\StringOutput::Hex => 'x', /** @phpstan-ignore class.notFound */
            default => '',
        };

        $outputOptions .= match ($this->oid_output_format) {
            \Snmp\OidOutput::Full => 'f', /** @phpstan-ignore class.notFound */
            \Snmp\OidOutput::Suffix => 's', /** @phpstan-ignore class.notFound */
            \Snmp\OidOutput::Ucd => 'u', /** @phpstan-ignore class.notFound */
            \Snmp\OidOutput::Numeric => 'n', /** @phpstan-ignore class.notFound */
            default => '',
        };

        if ($this->numeric_index) {
            $outputOptions .= 'b';
        }
        if ($this->enum_print) {
            $outputOptions .= 'e';
        }
        if ($this->escape_quotes) {
            $outputOptions .= 'E';
        }
        if ($this->quick_print) {
            $outputOptions .= 'Q';
        }
        if ($this->numeric_timeticks) {
            $outputOptions .= 't';
        }
        if ($this->print_hex_text) {
            $outputOptions .= 'T';
        }
        if ($this->dont_print_units) {
            $outputOptions .= 'U';
        }
        if ($this->extended_index) {
            $outputOptions .= 'X';
        }

        if ($outputOptions) {
            $ret[] = "-O$outputOptions";
        }

        return implode(' ', $ret);
    }
}
