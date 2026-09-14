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
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Util\Oid;
use Symfony\Component\Process\Process;

class NetSnmp implements SnmpBackendInterface, SnmpTranslatorInterface
{
    private readonly NetSnmpOptions $optionsParser;

    public function __construct(?NetSnmpOptions $optionsParser = null)
    {
        $this->optionsParser = $optionsParser ?? new NetSnmpOptions;
    }

    /**
     * @param  string[]  $oids
     */
    public function get(string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->runCommand($this->optionsParser->buildCli('snmpget', $target, $oids, $config, $options));
    }

    public function walk(string $target, string $oid, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->runCommand($this->optionsParser->buildCli('snmpwalk', $target, [$oid], $config, $options));
    }

    /**
     * @param  string[]  $oids
     */
    public function next(string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        return $this->runCommand($this->optionsParser->buildCli('snmpgetnext', $target, $oids, $config, $options));
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
