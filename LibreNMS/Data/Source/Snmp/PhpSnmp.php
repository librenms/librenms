<?php

/**
 * PhpSnmp.php
 *
 * Executes SNMP commands using the PHP SNMP extension, falling back to the Net-SNMP CLI utilities.
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
use LibreNMS\Enum\SnmpQuickPrint;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Util\Rewrite;

class PhpSnmp implements SnmpBackendInterface
{
    /**
     * @param  string[]  $oids
     */
    public function get(string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        $snmp = $this->buildSnmp('get', $target, $config, $options);

        return $snmp ? $this->runCommand('get', $snmp, $target, $config, $oids) : (new NetSnmp())->get($target, $oids, $config, $options);
    }

    public function walk(string $target, string $oid, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        $snmp = $this->buildSnmp('walk', $target, $config, $options);

        return $snmp ? $this->runCommand('walk', $snmp, $target, $config, [$oid]) : (new NetSnmp())->walk($target, $oid, $config, $options);
    }

    /**
     * @param  string[]  $oids
     */
    public function next(string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        $snmp = $this->buildSnmp('getnext', $target, $config, $options);

        return $snmp ? $this->runCommand('getnext', $snmp, $target, $config, $oids) : (new NetSnmp())->next($target, $oids, $config, $options);
    }

    /**
     * Credentials used per SNMPv3 security name in this process
     *
     * @var array<string, string>
     */
    private static array $v3Credentials = [];

    /**
     * MIB settings the MIB tree was last loaded with
     */
    private static ?string $loadedMibs = null;

    /**
     * Build a SNMP object from arguments, returns null if php-snmp can't handle this query
     */
    public function buildSnmp(string $cmd, string $target, SnmpConfig $config, SnmpQueryOptions $options): ?\SNMP
    {
        if (! $this->worksFor($cmd, $config, $options)) {
            return null;
        }

        $host = $this->resolveTarget($target);
        if ($host === null) {
            return null;
        }

        // the community parameter is the security name for v3
        if ($config->version === 'v3') {
            $community = (string) $config->authname;
        } else {
            $community = (string) $config->community;
            if ($options->context) {
                $community .= '@' . $options->context;
            }
        }

        // php-snmp rejects arguments it can't handle with ValueErrors or warnings (which may contain passphrases),
        // leave those to net-snmp so it reports the error
        set_error_handler(fn () => true, E_WARNING);
        try {
            $snmp = new \SNMP(
                $this->snmpver($config->version),
                "$host:$config->port",
                $community,
                (int) round($config->timeout * 1000000),
                $config->retries,
            );

            if ($config->version === 'v3' && ! $this->setSecurityOptions($snmp, $config, $options->context)) {
                return null;
            }
        } catch (\ValueError) {
            return null;
        } finally {
            restore_error_handler();
        }

        // net-snmp caches v3 users and their keys per engine for the life of the process, ignoring the credentials
        // of later sessions. Only use one set of credentials per security name, so cached keys always match.
        if ($config->version === 'v3') {
            $credentials = hash('sha256', serialize([
                strtolower((string) $config->authlevel),
                $this->algorithm($config->authalgo),
                $config->authpass,
                $this->algorithm($config->cryptoalgo),
                $config->cryptopass,
            ]));

            if ((self::$v3Credentials[$community] ??= $credentials) !== $credentials) {
                return null;
            }
        }

        $this->setOptions($snmp, $options);
        $this->initMibs($options);

        return $snmp;
    }

    /**
     * Does php-snmp work for the config/options
     */
    private function worksFor(string $cmd, SnmpConfig $config, SnmpQueryOptions $options): bool
    {
        if (! class_exists(\SNMP::class)) {
            return false;
        }

        // We need to be able to reset MIBs
        if (! function_exists('snmp_init_mib')) {
            return false;
        }

        if ($config->transport !== 'udp') {
            return false;
        }

        if (! in_array($config->version, ['v1', 'v2c', 'v3'])) {
            return false;
        }

        // php-snmp always walks with GETBULK on v2c/v3, so non-bulk walks need net-snmp
        if ($cmd === 'walk' && $config->version !== 'v1' && (! $config->bulk || ! $options->allowBulk)) {
            return false;
        }

        // php-snmp cannot disable display hints (-Ih)
        if (! $options->applyDisplayHints) {
            return false;
        }

        return true;
    }

    /**
     * Resolve the target to an IPv4 address, like net-snmp's udp transport.
     * php-snmp would also use IPv6 addresses, those need the udp6 transport.
     */
    private function resolveTarget(string $target): ?string
    {
        if (filter_var($target, FILTER_VALIDATE_IP)) {
            return filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $target : null;
        }

        $ip = gethostbyname($target);

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $ip : null;
    }

    /**
     * Set SNMPv3 security, unsupported algorithms throw a ValueError
     */
    private function setSecurityOptions(\SNMP $snmp, SnmpConfig $config, string $context): bool
    {
        $authalgo = $this->algorithm($config->authalgo);
        $cryptoalgo = $this->algorithm($config->cryptoalgo);

        return match (strtolower((string) $config->authlevel)) {
            'authpriv' => $snmp->setSecurity('authPriv', $authalgo, (string) $config->authpass, $cryptoalgo, (string) $config->cryptopass, $context),
            'authnopriv' => $snmp->setSecurity('authNoPriv', $authalgo, (string) $config->authpass, '', '', $context), /** @phpstan-ignore argument.type */
            'noauthnopriv' => $snmp->setSecurity('noAuthNoPriv', '', '', '', '', $context), /** @phpstan-ignore argument.type, argument.type */
            default => false,
        };
    }

    /**
     * Map LibreNMS algorithm names (SHA-256, AES-256-C) to php-snmp names (SHA256, AES256C)
     */
    private function algorithm(?string $algorithm): string
    {
        return str_replace('-', '', strtoupper((string) $algorithm));
    }

    /**
     * Set options on SNMP object
     */
    private function setOptions(\SNMP $snmp, SnmpQueryOptions $options): void
    {
        $snmp->oid_increasing_check = ! $options->tolerateUnorderedIndexes;
        $snmp->quick_print = ($options->quickPrint != SnmpQuickPrint::None);
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
        $mibDirs = implode(':', $options->mibDirs ?: [LibrenmsConfig::get('mib_dir')]);
        $mibs = implode(':', $options->mibs);

        // reloading the MIB tree is expensive, only do it when the settings change
        $state = serialize([$options->allowUnderscores, $mibDirs, $mibs]);
        if (self::$loadedMibs === $state) {
            return;
        }

        // Set the MIB allow underscore options
        snmp_set_mib_option(\Snmp\Mib::AllowUnderscores, $options->allowUnderscores); /** @phpstan-ignore function.notFound, class.notFound */

        // init_mib() loads the modules listed in MIBS (as net-snmp -m does) instead of the library defaults
        $previousMibs = getenv('MIBS');
        putenv("MIBS=$mibs");
        try {
            // Reset the loaded MIB tree with the configured mib dirs
            snmp_init_mib($mibDirs); /** @phpstan-ignore function.notFound */
        } finally {
            putenv($previousMibs === false ? 'MIBS' : "MIBS=$previousMibs");
        }

        self::$loadedMibs = $state;
    }

    /**
     * Run the command
     *
     * @param  string[]  $oids
     */
    private function runCommand(string $cmd, \SNMP $snmp, string $target, SnmpConfig $config, array $oids): SnmpResponse
    {
        // PHP-SNMP generates some errors - set the error handler to capture them
        $missing = [];
        $errors = '';
        $packetError = false;
        set_error_handler(function (int $err_severity, string $err_msg, string $err_filename, int $err_line) use (&$missing, &$errors, &$packetError): bool {
            if (preg_match('/\'([^\']+)\': (No Such Object available on this agent at this OID|No Such Instance currently exists at this OID|No more variables left in this MIB View \(It is past the end of the MIB tree\))/', $err_msg, $matches)) {
                $missing[$matches[1]] = $matches[2];
            } elseif (preg_match('/Invalid object identifier: (\S+)/', $err_msg, $matches)) {
                $errors .= "$matches[1]: Unknown Object Identifier\n";
            } elseif (preg_match('/Error in packet at (?:\'([^\']+)\'|\d+ object_id): (.+)/', $err_msg, $matches)) {
                // error status in the response (e.g. v1 noSuchName), formatted like net-snmp
                $packetError = true;
                $errors .= "Error in packet\nReason: $matches[2]\n" . ($matches[1] !== '' ? "Failed object: $matches[1]\n" : '');
            } else {
                // drop the "SNMP::get(): Fatal error: " prefix to match net-snmp messages
                $errors .= preg_replace('/^SNMP::\w+\(\): (Fatal error: )?/', '', $err_msg) . "\n";
            }

            return true;
        }, E_WARNING);

        try {
            $res = match ($cmd) {
                'get' => $snmp->get($oids),
                'getnext' => $snmp->getnext($oids),
                'walk' => $snmp->walk($oids, false, $config->maxRepeaters > 0 ? $config->maxRepeaters : 10, 0),
                default => throw new \Exception("SNMP command $cmd is not supported"),
            };
        } finally {
            restore_error_handler();
        }

        if ($res === false) {
            $res = [];
        }

        // trim values the same way net-snmp output is parsed (RawSnmpResponse)
        foreach ($res as $k => $v) {
            $v = (string) $v;
            $res[$k] = str_starts_with($v, '"') && str_ends_with($v, '"') ? trim(stripslashes($v), "\" \n\r") : trim($v);
        }

        foreach ($missing as $k => $v) {
            $res[$k] = $v;
        }

        // getErrno() holds the last error, translate it to net-snmp style stderr/exit code
        $exitCode = 0;
        switch ($snmp->getErrno()) {
            case \SNMP::ERRNO_NOERROR:
                break;
            case \SNMP::ERRNO_ERROR_IN_REPLY:
                // missing OIDs are returned as values (like net-snmp), net-snmp exits 2 for response errors
                $exitCode = $packetError ? 2 : ($errors ? 1 : 0);
                break;
            case \SNMP::ERRNO_TIMEOUT:
                // net-snmp ends the message with a period, except for walks
                $errors = "Timeout: No Response from $config->transport:" . Rewrite::addIpv6Brackets($target) . ":$config->port" . ($cmd === 'walk' ? '' : '.') . "\n";
                $exitCode = 1;
                break;
            case \SNMP::ERRNO_OID_NOT_INCREASING:
                $error = $snmp->getError();
                $errors = (str_starts_with($error, 'Error: OID not increasing') ? $error : "Error: OID not increasing: $error") . "\n";
                $exitCode = 1;
                break;
            default:
                $errors = $errors ?: $snmp->getError() . "\n";
                $exitCode = 1;
        }

        return new SnmpResponse(
            $res,
            $errors,
            $exitCode,
            ["php-snmp-$cmd", ...$oids],
        );
    }
}
