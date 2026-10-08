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
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;

class PhpSnmp implements SnmpBackendInterface
{
    private const MISSING_REGEX = '/\'([^\']+)\': (No Such Object available on this agent at this OID|No Such Instance currently exists at this OID|No more variables left in this MIB View \(It is past the end of the MIB tree\))/';

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

    public function __construct(
        private readonly NetSnmp $netSnmp = new NetSnmp,
    ) {
    }

    /**
     * @param  string[]  $oids
     */
    public function get(string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->query('get', $target, $oids, $config, $options)
            ?? $this->netSnmp->get($target, $oids, $config, $options);
    }

    public function walk(string $target, string $oid, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->query('walk', $target, [$oid], $config, $options)
            ?? $this->netSnmp->walk($target, $oid, $config, $options);
    }

    /**
     * @param  string[]  $oids
     */
    public function next(string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->query('getnext', $target, $oids, $config, $options)
            ?? $this->netSnmp->next($target, $oids, $config, $options);
    }

    /**
     * Run the query with php-snmp, returns null if php-snmp can't handle it
     *
     * @param  string[]  $oids
     */
    private function query(string $cmd, string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options): ?SnmpResponse
    {
        if (! $this->supports($cmd, $config, $options)) {
            return null;
        }

        // net-snmp's udp transport only uses IPv4, php-snmp would also use IPv6 addresses
        $host = IP::isValid($target) ? $target : gethostbyname($target);
        if (! IPv4::isValid($host)) {
            return new SnmpResponse([], "Unknown host ($config->transport:$target:$config->port)\n", 1, ["php-snmp-$cmd", ...$oids]);
        }

        $snmp = $this->createSession($host, $config, $options);
        if ($snmp === null) {
            return null;
        }

        $this->setOutputOptions($snmp, $options);
        $this->loadMibs($options);

        return $this->run($cmd, $snmp, $target, $config, $oids);
    }

    /**
     * Can php-snmp handle this query the same way net-snmp would
     */
    private function supports(string $cmd, SnmpConfig $config, SnmpQueryOptions $options): bool
    {
        // the extension must be able to reset MIBs
        if (! class_exists(\SNMP::class) || ! function_exists('snmp_init_mib')) {
            return false;
        }

        if ($config->transport !== 'udp' || ! in_array($config->version, ['v1', 'v2c', 'v3'])) {
            return false;
        }

        // php-snmp always walks with GETBULK on v2c/v3, so non-bulk walks need net-snmp
        if ($cmd === 'walk' && $config->version !== 'v1' && (! $config->bulk || ! $options->allowBulk)) {
            return false;
        }

        // php-snmp cannot disable display hints (-Ih)
        return $options->applyDisplayHints;
    }

    /**
     * Create the SNMP session, returns null for settings php-snmp rejects (net-snmp will report those errors)
     */
    private function createSession(string $host, SnmpConfig $config, SnmpQueryOptions $options): ?\SNMP
    {
        if ($config->version === 'v3') {
            // the community parameter is the security name for v3
            $community = (string) $config->authname;
        } else {
            $community = $options->context ? "$config->community@$options->context" : (string) $config->community;
        }

        // php-snmp reports bad settings as ValueErrors or warnings (which may contain passphrases)
        set_error_handler(fn () => true, E_WARNING);
        try {
            $snmp = new \SNMP(
                match ($config->version) {
                    'v1' => \SNMP::VERSION_1,
                    'v2c' => \SNMP::VERSION_2c,
                    'v3' => \SNMP::VERSION_3,
                    default => throw new \LogicException("Unsupported SNMP version $config->version"),
                },
                "$host:$config->port",
                $community,
                (int) round($config->timeout * 1000000),
                $config->retries,
            );

            if ($config->version === 'v3' && ! $this->setSecurity($snmp, $config, $options->context)) {
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

        return $snmp;
    }

    /**
     * Set SNMPv3 security, unsupported algorithms throw a ValueError
     */
    private function setSecurity(\SNMP $snmp, SnmpConfig $config, string $context): bool
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

    private function setOutputOptions(\SNMP $snmp, SnmpQueryOptions $options): void
    {
        $snmp->oid_increasing_check = ! $options->tolerateUnorderedIndexes;
        $snmp->quick_print = $options->quickPrint !== SnmpQuickPrint::None;
        $snmp->enum_print = $options->numericEnums;
        $snmp->numeric_index = $options->numericIndexes; /** @phpstan-ignore property.notFound */
        $snmp->numeric_timeticks = $options->numericTimeticks; /** @phpstan-ignore property.notFound */
        $snmp->extended_index = $options->extendedIndex; /** @phpstan-ignore property.notFound */
        $snmp->dont_print_units = ! $options->printUnits; /** @phpstan-ignore property.notFound */
        $snmp->escape_quotes = $options->escapeQuotes; /** @phpstan-ignore property.notFound */
        $snmp->print_hex_text = $options->printHexText; /** @phpstan-ignore property.notFound */
        $snmp->setStringOutputFormat(match ($options->stringFormat) { /** @phpstan-ignore method.notFound */
            SnmpStringOutput::Guess => \Snmp\StringOutput::Guess, /** @phpstan-ignore class.notFound */
            SnmpStringOutput::Ascii => \Snmp\StringOutput::Ascii, /** @phpstan-ignore class.notFound */
            SnmpStringOutput::Hex => \Snmp\StringOutput::Hex, /** @phpstan-ignore class.notFound */
        });
        $snmp->setOidOutputFormat(match ($options->oidFormat) { /** @phpstan-ignore method.notFound */
            SnmpOidOutput::Full => \Snmp\OidOutput::Full, /** @phpstan-ignore class.notFound */
            SnmpOidOutput::Numeric => \Snmp\OidOutput::Numeric, /** @phpstan-ignore class.notFound */
            SnmpOidOutput::Module => \Snmp\OidOutput::Module, /** @phpstan-ignore class.notFound */
            SnmpOidOutput::Suffix => \Snmp\OidOutput::Suffix, /** @phpstan-ignore class.notFound */
            SnmpOidOutput::Ucd => \Snmp\OidOutput::Ucd, /** @phpstan-ignore class.notFound */
        });
    }

    /**
     * Load the MIBs for this query into the process wide MIB tree
     */
    private function loadMibs(SnmpQueryOptions $options): void
    {
        $mibDirs = implode(':', $options->mibDirs ?: [LibrenmsConfig::get('mib_dir')]);
        $mibs = implode(':', $options->mibs);

        // reloading the MIB tree is expensive, only do it when the settings change
        $state = serialize([$options->allowUnderscores, $mibDirs, $mibs]);
        if (self::$loadedMibs === $state) {
            return;
        }

        snmp_set_mib_option(\Snmp\Mib::AllowUnderscores, $options->allowUnderscores); /** @phpstan-ignore function.notFound, class.notFound */

        // init_mib() loads the modules listed in MIBS (as net-snmp -m does) instead of the library defaults
        $previousMibs = getenv('MIBS');
        putenv("MIBS=$mibs");
        try {
            snmp_init_mib($mibDirs); /** @phpstan-ignore function.notFound */
        } finally {
            putenv($previousMibs === false ? 'MIBS' : "MIBS=$previousMibs");
        }

        self::$loadedMibs = $state;
    }

    /**
     * Run the command and translate the result to match net-snmp
     *
     * @param  string[]  $oids
     */
    private function run(string $cmd, \SNMP $snmp, string $target, SnmpConfig $config, array $oids): SnmpResponse
    {
        // php-snmp reports errors as warnings, collect them
        $missing = [];
        $errors = '';
        $packetError = false;
        set_error_handler(function (int $severity, string $message) use (&$missing, &$errors, &$packetError): bool {
            if (preg_match(self::MISSING_REGEX, $message, $matches)) {
                // net-snmp returns these as values
                $missing[$matches[1]] = $matches[2];
            } elseif (preg_match('/Invalid object identifier: (\S+)/', $message, $matches)) {
                $errors .= "$matches[1]: Unknown Object Identifier\n";
            } elseif (preg_match('/Error in packet at (?:\'([^\']+)\'|\d+ object_id): (.+)/', $message, $matches)) {
                // error status in the response (e.g. v1 noSuchName)
                $packetError = true;
                $errors .= "Error in packet\nReason: $matches[2]\n" . ($matches[1] !== '' ? "Failed object: $matches[1]\n" : '');
            } else {
                $errors .= preg_replace('/^SNMP::\w+\(\): (Fatal error: )?/', '', $message) . "\n";
            }

            return true;
        }, E_WARNING);

        try {
            $values = match ($cmd) {
                'get' => $snmp->get($oids),
                'getnext' => $snmp->getnext($oids),
                'walk' => $snmp->walk($oids, false, $config->maxRepeaters > 0 ? $config->maxRepeaters : 10, 0),
                default => throw new \LogicException("Unsupported SNMP command $cmd"),
            };
        } finally {
            restore_error_handler();
        }

        // trim values the same way net-snmp output is parsed (RawSnmpResponse)
        $values = array_map(function ($value) {
            $value = (string) $value;

            return str_starts_with($value, '"') && str_ends_with($value, '"') ? trim(stripslashes($value), "\" \n\r") : trim($value);
        }, $values ?: []) + $missing;

        // getErrno() holds the last error
        [$exitCode, $errors] = match ($snmp->getErrno()) {
            \SNMP::ERRNO_NOERROR => [0, $errors],
            // net-snmp exits 2 for error status in the response, missing OIDs are not errors
            \SNMP::ERRNO_ERROR_IN_REPLY => [$packetError ? 2 : ($errors ? 1 : 0), $errors],
            // net-snmp ends the message with a period, except for walks
            \SNMP::ERRNO_TIMEOUT => [1, "Timeout: No Response from $config->transport:$target:$config->port" . ($cmd === 'walk' ? '' : '.') . "\n"],
            \SNMP::ERRNO_OID_NOT_INCREASING => [1, $snmp->getError() . "\n"],
            default => [1, $errors ?: $snmp->getError() . "\n"],
        };

        return new SnmpResponse($values, $errors, $exitCode, ["php-snmp-$cmd", ...$oids]);
    }
}
