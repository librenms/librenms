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
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Util\Oid;
use LibreNMS\Util\Rewrite;
use Symfony\Component\Process\Process;

class NetSnmp implements SnmpBackendInterface, SnmpTranslateBackendInterface
{
    public function get(SnmpTarget $target, array $oids, SnmpQueryOptions $options, string $context): SnmpResponse
    {
        $cliCommand = $this->buildCli('snmpget', $target, $oids, $options, $context);

        return $this->runCommand($cliCommand);
    }

    public function walk(SnmpTarget $target, string $oid, SnmpQueryOptions $options, string $context): SnmpResponse
    {
        $command = $options->bulk ? 'snmpbulkwalk' : 'snmpwalk';
        $cliCommand = $this->buildCli($command, $target, [$oid], $options, $context);

        return $this->runCommand($cliCommand);
    }

    public function next(SnmpTarget $target, array $oids, SnmpQueryOptions $options, string $context): SnmpResponse
    {
        $cliCommand = $this->buildCli('snmpgetnext', $target, $oids, $options, $context);

        return $this->runCommand($cliCommand);
    }

    public function translate(string $oid, SnmpQueryOptions $options): string
    {
        $oidObj = new Oid($oid);
        $cmd = [LibrenmsConfig::get('snmptranslate', 'snmptranslate')];
        array_push($cmd, '-M', implode(':', $options->mibDirs ?: [LibrenmsConfig::get('mib_dir')]));
        array_push($cmd, '-m', implode(':', $options->mibs));

        if ($options->outputOidsNumerically) {
            $cmd[] = '-On';
        } elseif (! $options->outputMibNames) {
            $cmd[] = '-Os';
        } else {
            $cmd[] = '-OS';
        }

        if (! $oidObj->hasMib() && ! $oidObj->hasNumericRoot()) {
            $cmd[] = '-IR';
        }

        $cmd[] = $oid;

        $proc = new Process($cmd);
        $proc->setTimeout((int) LibrenmsConfig::get('snmp.exec_timeout', 1200));
        $proc->run();

        return (new SnmpResponse(
            $proc->getOutput(),
            $proc->getErrorOutput(),
            $proc->getExitCode(),
        ))->value();
    }

    public function buildCli(string $command, SnmpTarget $target, array $oids, SnmpQueryOptions $options, string $context): array
    {
        $cmd = $this->initCommand($command, $target, $options);

        array_push($cmd, '-M', implode(':', $options->mibDirs ?: [LibrenmsConfig::get('mib_dir')]));
        array_push($cmd, '-m', implode(':', $options->mibs));

        $this->buildAuth($cmd, $target, $context);

        $cmd = array_merge($cmd, $this->buildOutputFlags($options));

        if ($target->config->timeout !== 1) {
            array_push($cmd, '-t', (string) $target->config->timeout);
        }

        if ($target->config->retries !== 5) {
            array_push($cmd, '-r', (string) $target->config->retries);
        }

        $hostname = Rewrite::addIpv6Brackets($target->hostname);
        $transport = $target->config->transport ?: 'udp';
        $port = $target->config->port ?: 161;
        $cmd[] = "$transport:$hostname:$port";

        return array_merge($cmd, $oids);
    }

    private function initCommand(string $command, SnmpTarget $target, SnmpQueryOptions $options): array
    {
        if ($command === 'snmpbulkwalk') {
            $cmd = [LibrenmsConfig::get('snmpbulkwalk', 'snmpbulkwalk')];
            if ($target->config->maxRepeaters > 0) {
                $cmd[] = "-Cr{$target->config->maxRepeaters}";
            }

            return $cmd;
        }

        return [LibrenmsConfig::get($command, $command)];
    }

    private function buildAuth(array &$cmd, SnmpTarget $target, string $context): void
    {
        $config = $target->config;
        $context = $context ?: ($config->context ?? '');

        if ($config->version === 'v3') {
            array_push($cmd, '-v3', '-l', (string) $config->authlevel);
            array_push($cmd, '-n', $context);

            switch (strtolower((string) $config->authlevel)) {
                case 'authpriv':
                    array_push($cmd, '-x', (string) $config->cryptoalgo);
                    array_push($cmd, '-X', (string) $config->cryptopass);
                    // fallthrough
                case 'authnopriv':
                    array_push($cmd, '-a', (string) $config->authalgo);
                    array_push($cmd, '-A', (string) $config->authpass);
                    // fallthrough
                case 'noauthnopriv':
                    array_push($cmd, '-u', (string) ($config->authname ?: 'root'));
                    break;
                default:
                    Log::debug("Unsupported SNMPv3 AuthLevel: {$config->authlevel}");
            }
        } elseif ($config->version === 'v2c' || $config->version === 'v1') {
            array_push($cmd, '-' . $config->version, '-c', $context ? "{$config->community}@$context" : (string) $config->community);
        } else {
            Log::debug("Unsupported SNMP Version: {$config->version}");
        }
    }

    private function buildOutputFlags(SnmpQueryOptions $options): array
    {
        $baseFlag = $options->outputEnumsAsStrings ? '-OQXUt' : '-OQXUte';
        $flags = [$baseFlag, '-Pu'];

        if ($options->outputOidsNumerically) {
            $flags[] = '-On';
        }

        if ($options->outputIndexesNumerically) {
            $flags[] = '-Ob';
        }

        if (! $options->outputMibNames) {
            $flags[] = '-Os';
        }

        if ($options->tolerateUnorderedIndexes) {
            $flags[] = '-Cc';
        }

        return $flags;
    }

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
