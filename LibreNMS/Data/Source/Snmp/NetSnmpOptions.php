<?php

namespace LibreNMS\Data\Source\Snmp;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Arr;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Exceptions\SnmpVersionUnsupportedException;
use LibreNMS\Exceptions\UnsupportedSnmpOption;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Util\Mib;
use LibreNMS\Util\Rewrite;

class NetSnmpOptions
{
    /**
     * Generate a net-snmp command line
     *
     * @param  string[]  $oids
     * @return string[]
     *
     * @throws SnmpException
     */
    public function buildCli(string $command, string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options, string $os = ''): array
    {
        if ($command === 'snmpwalk' && $config->bulk && $options->allowBulk && $config->version !== 'v1') {
            $command = 'snmpbulkwalk';
        }

        $cmd = [
            LibrenmsConfig::get($command, $command),
            '-M', implode(':', $options->mibDirs ?: [LibrenmsConfig::get('mib_dir')]),
            '-m', implode(':', $options->mibs),
            ...$this->buildAuth($config, $options->context),
            ...$this->buildOutputFlags($options),
        ];

        if ($command === 'snmpbulkwalk' && $config->maxRepeaters > 0) {
            $cmd[] = "-Cr$config->maxRepeaters";
        }

        if ($options->tolerateUnorderedIndexes) {
            $cmd[] = '-Cc';
        }

        if ($config->timeout > 0 && $config->timeout != 1) {
            array_push($cmd, '-t', (string) $config->timeout);
        }

        if ($config->retries !== 5) {
            array_push($cmd, '-r', (string) $config->retries);
        }

        $formattedTarget = sprintf('%s:%s:%s', $config->transport, Rewrite::addIpv6Brackets($target), $config->port);

        return [...$cmd, $formattedTarget, ...$oids];
    }

    /**
     * @return string[]
     */
    public function buildOutputFlags(SnmpQueryOptions $options): array
    {
        $opts = '';

        if ($options->quickPrint) {
            $opts .= 'Q';
        }

        if ($options->extendedIndex) {
            $opts .= 'X';
        }

        if (! $options->printUnits) {
            $opts .= 'U';
        }

        if ($options->numericTimeticks) {
            $opts .= 't';
        }

        if ($options->numericEnums) {
            $opts .= 'e';
        }

        if ($options->numericIndexes) {
            $opts .= 'b';
        }

        if ($options->escapeQuotes) {
            $opts .= 'E';
        }

        if ($options->printHexText) {
            $opts .= 'T';
        }

        $opts .= match ($options->stringFormat) {
            SnmpStringOutput::Ascii => 'a',
            SnmpStringOutput::Hex => 'x',
            default => '',
        };

        $opts .= match ($options->oidFormat) {
            SnmpOidOutput::Full => 'f',
            SnmpOidOutput::Suffix => 's',
            SnmpOidOutput::Ucd => 'u',
            SnmpOidOutput::Numeric => 'n',
            default => '',
        };

        $flags = [];

        if ($opts !== '') {
            $flags[] = "-O$opts";
        }

        if ($options->allowUnderscores) {
            $flags[] = '-Pu';
        }

        if (! $options->applyDisplayHints) {
            $flags[] = '-Ih';
        }

        return $flags;
    }

    /**
     * @return string[]
     *
     * @throws SnmpException
     */
    public function buildAuth(SnmpConfig $config, ?string $context = null): array
    {
        if ($config->version === 'v2c' || $config->version === 'v1') {
            return [
                "-$config->version",
                '-c',
                $context ? "$config->community@$context" : (string) $config->community,
            ];
        }

        if ($config->version === 'v3') {
            $auth = match (strtolower((string) $config->authlevel)) {
                'authpriv' => [
                    '-v3',
                    '-l', (string) $config->authlevel,
                    '-x', (string) $config->cryptoalgo,
                    '-X', (string) $config->cryptopass,
                    '-a', (string) $config->authalgo,
                    '-A', (string) $config->authpass,
                    '-u', $config->authname ?: 'root',
                ],
                'authnopriv' => [
                    '-v3',
                    '-l', (string) $config->authlevel,
                    '-a', (string) $config->authalgo,
                    '-A', (string) $config->authpass,
                    '-u', $config->authname ?: 'root',
                ],
                'noauthnopriv' => [
                    '-v3',
                    '-l', (string) $config->authlevel,
                    '-u', $config->authname ?: 'root',
                ],
                default => throw new SnmpException("Unsupported SNMPv3 AuthLevel: $config->authlevel"),
            };

            if ($context) {
                array_push($auth, '-n', $context);
            }

            return $auth;
        }

        throw new SnmpVersionUnsupportedException($config->version);
    }

    /**
     * @param  string[]|string|null  $args
     *
     * @throws UnsupportedSnmpOption
     */
    public function parseCli(array|string|null $args): SnmpQueryOptions
    {
        if ($args === null) {
            return SnmpQueryOptions::quickPrint();
        }

        $options = new SnmpQueryOptions();

        $arguments = Arr::wrap($args);

        for ($i = 0; $i < count($arguments); $i++) {
            $arg = $arguments[$i];
            if (! is_string($arg)) {
                continue;
            }

            $prefix = substr($arg, 0, 2);
            $suffix = substr($arg, 2);

            if ($prefix === '-O') {
                foreach (str_split($suffix) as $outopt) {
                    match ($outopt) {
                        'a' => $options->stringFormat = SnmpStringOutput::Ascii,
                        'x' => $options->stringFormat = SnmpStringOutput::Hex,
                        'f' => $options->oidFormat = SnmpOidOutput::Full,
                        's' => $options->oidFormat = SnmpOidOutput::Suffix,
                        'S' => $options->oidFormat = SnmpOidOutput::Module,
                        'u' => $options->oidFormat = SnmpOidOutput::Ucd,
                        'n' => $options->oidFormat = SnmpOidOutput::Numeric,
                        'b' => $options->numericIndexes = true,
                        'e' => $options->numericEnums = true,
                        'E' => $options->escapeQuotes = true,
                        'Q' => $options->quickPrint = true,
                        't' => $options->numericTimeticks = true,
                        'T' => $options->printHexText = true,
                        'U' => $options->printUnits = false,
                        'X' => $options->extendedIndex = true,
                        default => throw new UnsupportedSnmpOption("Unknown option -O$outopt"),
                    };
                }
            } elseif ($prefix === '-C') {
                if (str_contains($suffix, 'c')) {
                    $options->tolerateUnorderedIndexes = true;
                }
            } elseif ($prefix === '-P') {
                $options->allowUnderscores = str_contains($suffix, 'u');
            } elseif ($prefix === '-I') {
                if (str_contains($suffix, 'h')) {
                    $options->applyDisplayHints = false;
                }
            } elseif ($prefix === '-m') {
                $options->mibs = $this->parseMibOption($suffix, $arguments, $i, $options->mibs);
            } elseif ($prefix === '-M') {
                $options->mibDirs = $this->parseMibOption($suffix, $arguments, $i, $options->mibDirs);
            }
        }

        return $options;
    }

    /**
     * @param  string[]  $args
     * @param  string[]  $existing
     * @return string[]
     */
    private function parseMibOption(string $suffix, array $args, int &$i, array $existing): array
    {
        $useNext = ! $suffix && isset($args[$i + 1]) && is_string($args[$i + 1]);

        return Mib::parseCliInput($useNext ? $args[++$i] : $suffix, $existing);
    }
}
