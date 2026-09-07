<?php

/**
 * PhpSnmp.php
 *
 * Executes SNMP commands using the Net-SNMP CLI utilities.
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Data\Source\Snmp;

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Util\Rewrite;

class PhpSnmp implements SnmpBackendInterface
{
    /**
     * @param  string[]  $oids
     */
    public function get(SnmpConfig $config, array $oids, SnmpQueryOptions $options): SnmpResponse
    {
        $snmp = $this->buildSnmp($config, $options);

        return $snmp ? $this->runCommand('get', $snmp, $config, $oids, $options) : (new NetSnmp())->get($config, $oids, $options);
    }

    public function walk(SnmpConfig $config, string $oid, SnmpQueryOptions $options): SnmpResponse
    {
        $snmp = $this->buildSnmp($config, $options);

        return $snmp ? $this->runCommand('walk', $snmp, $config, [$oid], $options) : (new NetSnmp())->walk($config, $oid, $options);
    }

    /**
     * @param  string[]  $oids
     */
    public function next(SnmpConfig $config, array $oids, SnmpQueryOptions $options): SnmpResponse
    {
        $snmp = $this->buildSnmp($config, $options);

        return $snmp ? $this->runCommand('next', $snmp, $config, $oids, $options) : (new NetSnmp())->get($config, $oids, $options);
    }

    /**
     * Build a SNMP object from arguments
     */
    public function buildSnmp(SnmpConfig $config, SnmpQueryOptions $options): ?\SNMP
    {
        if (! $this->worksFor($config, $options)) {
            return null;
        }

        $community = $config->community ?: 'public';
        if ($options->context) {
            $community .= '@' . $options->context;
        }

        $snmp = new \SNMP(
            $this->snmpver($config->version),
            Rewrite::addIpv6Brackets($config->target) . ':' . $config->port,
            $config->community ?: 'public',
            $config->timeout * 1000000,
            $config->retries,
        );

        $this->setSecurityOptions($snmp, $config, $options->context);
        $this->setOptions($snmp, $options);
        $this->initMibs($options);

        return $snmp;
    }

    /**
     * Does php-snmp work for the config/options
     */
    private function worksFor(SnmpConfig $config, SnmpQueryOptions $options): bool
    {
        if (! class_exists('\SNMP')) {
            return false;
        }

        // We need to be able to reset MIBs
        if (! function_exists('snmp_init_mib')) {
            return false;
        }

        if ($config->transport !== 'udp') {
            return false;
        }

        if ($config->version == 'v3') {
            // Dummy SNMP object to check security settings are supported
            $snmp = new \SNMP(\SNMP::VERSION_3, 'localhost', 'root', 1000000, 3);
            try {
                $this->setSecurityOptions($snmp, $config, $options->context);
            } catch (\Exception) {
                return false;
            }
        }

        return true;
    }

    private function setSecurityOptions(\SNMP $snmp, SnmpConfig $config, ?string $context): void
    {
        if ($config->authlevel === 'authpriv') {
            $snmp->setSecurity('authPriv', $config->authalgo, $config->authpass, $config->cryptoalgo, $config->cryptopass, $context ?: '');
        } elseif ($config->authlevel === 'authnopriv') {
            $snmp->setSecurity('authNoPriv', $config->authalgo, $config->authpass, '', '', $context ?: ''); /** @phpstan-ignore argument.type */
        } else {
            $snmp->setSecurity('noAuthNoPriv', '', '', '', '', $context ?: ''); /** @phpstan-ignore argument.type, argument.type */
        }
    }

    /**
     * Set options on SNMP object
     */
    private function setOptions(\SNMP $snmp, SnmpQueryOptions $options): void
    {
        $snmp->oid_increasing_check = ! $options->tolerateUnorderedIndexes;
        $snmp->quick_print = $options->quickPrint;
        $snmp->enum_print = $options->numericEnums;
        $snmp->numeric_index = $options->numericIndexes; /** @phpstan-ignore property.notFound */
        $snmp->numeric_timeticks = $options->numericTimeticks; /** @phpstan-ignore property.notFound */
        $snmp->extended_index = $options->extendedIndex; /** @phpstan-ignore property.notFound */
        $snmp->dont_print_units = ! $options->printUnits; /** @phpstan-ignore property.notFound */
        $snmp->escape_quotes = $options->escapeQuotes; /** @phpstan-ignore property.notFound */
        $snmp->print_hex_text = $options->printHexText; /** @phpstan-ignore property.notFound */
        $snmp->setStringOutputFormat($this->getStringOutput($options->stringFormat)); /** @phpstan-ignore method.notFound */
        $snmp->setOidOutputFormat($this->getOidOutput($options->oidFormat)); /** @phpstan-ignore method.notFound */
    }

    private function snmpver(string $version): int
    {
        return match ($version) {
            'v1' => \SNMP::VERSION_1,
            'v2c' => \SNMP::VERSION_2c,
            'v3' => \SNMP::VERSION_3,
            default => throw new \Exception("SNMP version $version is not supported"),
        };
    }

    private function getOidOutput(SnmpOidOutput $type): \Snmp\OidOutput /** @phpstan-ignore class.notFound */
    {
        return match ($type) {
            SnmpOidOutput::Full => \Snmp\OidOutput::Full, /** @phpstan-ignore class.notFound */
            SnmpOidOutput::Suffix => \Snmp\OidOutput::Suffix, /** @phpstan-ignore class.notFound */
            SnmpOidOutput::Ucd => \Snmp\OidOutput::Ucd, /** @phpstan-ignore class.notFound */
            SnmpOidOutput::Numeric => \Snmp\OidOutput::Numeric, /** @phpstan-ignore class.notFound */
            default => \Snmp\OidOutput::Module, /** @phpstan-ignore class.notFound */
        };
    }

    private function getStringOutput(SnmpStringOutput $type): \Snmp\StringOutput /** @phpstan-ignore class.notFound */
    {
        return match ($type) {
            SnmpStringOutput::Ascii => \Snmp\StringOutput::Ascii, /** @phpstan-ignore class.notFound */
            SnmpStringOutput::Hex => \Snmp\StringOutput::Hex, /** @phpstan-ignore class.notFound */
            default => \Snmp\StringOutput::Guess, /** @phpstan-ignore class.notFound */
        };
    }

    private function initMibs(SnmpQueryOptions $options): void
    {
        // Set the MIB allow underscore options
        snmp_set_mib_option(\Snmp\Mib::AllowUnderscores, $options->allowUnderscores); /** @phpstan-ignore function.notFound, class.notFound */

        // Reset the loaded MIB tree with the configured mib dirs
        snmp_init_mib(implode(':', $options->mibDirs ?: [LibrenmsConfig::get('mib_dir')])); /** @phpstan-ignore function.notFound */

        // Load all explicit MIBs
        foreach ($options->mibs as $mib) {
            foreach (($options->mibDirs ?: [LibrenmsConfig::get('mib_dir')]) as $dir) {
                $mibfile = "$dir/$mib";
                if (file_exists($mibfile)) {
                    snmp_read_mib($mibfile);

                    break;
                }
            }
        }
    }

    /**
     * Run the command
     */
    /**
     * @param  string[]  $oids
     */
    private function runCommand(string $cmd, \SNMP $snmp, SnmpConfig $config, array $oids, SnmpQueryOptions $options): SnmpResponse
    {
        // PHP-SNMP generates some errors - set the error handler to capture them
        $missing = [];
        $errors = '';
        set_error_handler(function (int $err_severity, string $err_msg, string $err_filename, int $err_line) use (&$missing, &$errors): bool {
            if (preg_match('/\'([^\']+)\': (No Such Object available on this agent at this OID|No Such Instance currently exists at this OID)/', $err_msg, $matches)) {
                $missing[$matches[1]] = $matches[2];
            } elseif (preg_match('/Invalid object identifier: (\S+)/', $err_msg, $matches)) {
                $errors .= "$matches[1]: Unknown Object Identifier\n";
            } else {
                $errors .= "$err_msg\n";
            }

            return true;
        }, E_WARNING);

        $res = match ($cmd) {
            'get' => $snmp->get($oids),
            'getnext' => $snmp->getnext($oids),
            'walk' => $snmp->walk($oids, false, $config->maxRepeaters > 0 ? $config->maxRepeaters : 10, 0),
            default => throw new \Exception("SNMP command $cmd is not supported"),
        };

        restore_error_handler();

        $res_str = '';
        if ($res) {
            foreach ($res as $k => $v) {
                $res_str .= "$k = $v\n";
            }
        }
        foreach ($missing as $k => $v) {
            $res_str .= "$k = $v\n";
        }

        return new SnmpResponse(
            $res_str,
            $errors,
            $errors ? 1 : 0,
            ["php-snmp-$cmd", ...$oids],
        );
    }
}
