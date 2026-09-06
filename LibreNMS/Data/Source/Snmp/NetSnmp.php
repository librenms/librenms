<?php

/**
 * NetSnmp.php
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

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\Util\Oid;
use LibreNMS\Util\Rewrite;
use Symfony\Component\Process\Process;

class NetSnmp implements SnmpBackendInterface, SnmpTranslatorInterface
{
    /**
     * @param  string[]  $oids
     */
    public function get(SnmpTarget $target, array $oids, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->runCommand($this->buildCli('snmpget', $target, $oids, $options));
    }

    public function walk(SnmpTarget $target, string $oid, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->runCommand($this->buildCli('snmpwalk', $target, [$oid], $options));
    }

    /**
     * @param  string[]  $oids
     */
    public function next(SnmpTarget $target, array $oids, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->runCommand($this->buildCli('snmpgetnext', $target, $oids, $options));
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
    public function buildCli(string $command, SnmpTarget $target, array $oids, SnmpQueryOptions $options): array
    {
        $config = $target->config;

        if ($command === 'snmpwalk' && $options->allowBulk && $config->version !== 'v1') {
            $command = 'snmpbulkwalk';
        }

        $cmd = [
            LibrenmsConfig::get($command, $command),
            '-M', implode(':', $options->mibDirs ?: [LibrenmsConfig::get('mib_dir')]),
            '-m', implode(':', $options->mibs),
            ...$this->buildAuth($target, $options),
            ...$this->buildOutputFlags($options),
        ];

        if ($command === 'snmpbulkwalk' && $config->maxRepeaters > 0) {
            $cmd[] = "-Cr$config->maxRepeaters";
        }

        if ($options->tolerateUnorderedIndexes) {
            $cmd[] = '-Cc';
        }

        if ($options->includeGivenOid) {
            $cmd[] = '-Ci';
        }

        if ($config->timeout > 0 && $config->timeout != 1) {
            array_push($cmd, '-t', (string) $config->timeout);
        }

        if ($config->retries !== 5) {
            array_push($cmd, '-r', (string) $config->retries);
        }

        $hostname = Rewrite::addIpv6Brackets($target->hostname);
        $cmd[] = "$config->transport:$hostname:$config->port";

        return [...$cmd, ...$oids];
    }

    /**
     * @return string[]
     */
    private function buildOutputFlags(SnmpQueryOptions $options): array
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
     */
    private function buildAuth(SnmpTarget $target, SnmpQueryOptions $options): array
    {
        $config = $target->config;

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
                    '-x', (string) $config->cryptoalgo,
                    '-X', (string) $config->cryptopass,
                    '-a', (string) $config->authalgo,
                    '-A', (string) $config->authpass,
                    '-u', $config->authname ?: 'root',
                ],
                'authnopriv' => [
                    '-a', (string) $config->authalgo,
                    '-A', (string) $config->authpass,
                    '-u', $config->authname ?: 'root',
                ],
                'noauthnopriv' => [
                    '-u', $config->authname ?: 'root',
                ],
                default => [],
            };

            if ($auth === []) {
                Log::debug("Unsupported SNMPv3 AuthLevel: $config->authlevel");
            }

            return [
                '-v3',
                '-l', (string) $config->authlevel,
                '-n', $options->context,
                ...$auth,
            ];
        }

        Log::debug("Unsupported SNMP Version: $config->version");

        return [];
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
