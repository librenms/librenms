<?php

/**
 * NetSnmp.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Snmp;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Str;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Exceptions\SnmpVersionUnsupportedException;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Util\Oid;
use LibreNMS\Util\Rewrite;
use Symfony\Component\Process\Process;

class NetSnmp implements SnmpBackendInterface, SnmpTranslatorInterface
{
    /**
     * @param  string[]  $oids
     */
    public function get(SnmpConfig $config, array $oids, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->runCommand($this->buildCli('snmpget', $config, $oids, $options));
    }

    public function walk(SnmpConfig $config, string $oid, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->runCommand($this->buildCli('snmpwalk', $config, [$oid], $options));
    }

    /**
     * @param  string[]  $oids
     */
    public function next(SnmpConfig $config, array $oids, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->runCommand($this->buildCli('snmpgetnext', $config, $oids, $options));
    }

    public function translate(string $oid, SnmpQueryOptions $options): string
    {
        $oidObj = new Oid($oid);

        if ($options->oidFormat == SnmpOidOutput::Numeric && $oidObj->isNumeric()) {
            return Str::start($oid, '.');
        }

        $cmd = [
            LibrenmsConfig::get('snmptranslate', 'snmptranslate'),
            '-M', implode(':', $options->mibDirs ?: [LibrenmsConfig::get('mib_dir')]),
            '-m', implode(':', $options->mibs),
            $options->oidFormat == SnmpOidOutput::Numeric ? '-On' : ($options->oidFormat == SnmpOidOutput::Module ? '-OS' : '-Os'),
        ];

        if (! $oidObj->hasMib() && ! $oidObj->hasNumericRoot()) {
            $cmd[] = '-IR';
        }

        $cmd[] = $oid;

        return $this->runCommand($cmd)->value();
    }

    /**
     * Generate a net-snmp command line
     *
     * @param  string[]  $oids
     * @return string[]
     */
    public static function buildCli(string $command, SnmpConfig $config, array $oids, SnmpQueryOptions $options): array
    {
        if ($command === 'snmpwalk' && $config->bulk && $options->allowBulk && $config->version !== 'v1') {
            $command = 'snmpbulkwalk';
        }

        $cmd = [
            LibrenmsConfig::get($command, $command),
            '-M', implode(':', $options->mibDirs ?: [LibrenmsConfig::get('mib_dir')]),
            '-m', implode(':', $options->mibs),
            ...self::buildAuth($config, $options),
            ...self::buildOutputFlags($options),
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

        $hostname = Rewrite::addIpv6Brackets($config->target);
        $cmd[] = "$config->transport:$hostname:$config->port";

        return [...$cmd, ...$oids];
    }

    /**
     * @return string[]
     */
    private static function buildOutputFlags(SnmpQueryOptions $options): array
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
    private static function buildAuth(SnmpConfig $config, SnmpQueryOptions $options): array
    {
        if ($config->version === 'v2c' || $config->version === 'v1') {
            return [
                "-$config->version",
                '-c',
                $options->context ? "$config->community@$options->context" : (string) $config->community,
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

            if ($options->context) {
                array_push($auth, '-n', $options->context);
            }

            return $auth;
        }

        throw new SnmpVersionUnsupportedException($config->version);
    }

    /**
     * @param  string[]  $cliCommand
     */
    private function runCommand(array $cliCommand): SnmpResponse
    {
        $proc = new Process($cliCommand);
        $proc->setTimeout((int) LibrenmsConfig::get('snmp.exec_timeout', 1200));
        $proc->run();

        return new SnmpResponse(
            $proc->getOutput(),
            $proc->getErrorOutput(),
            $proc->getExitCode(),
            $cliCommand,
        );
    }
}
