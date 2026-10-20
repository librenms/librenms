<?php

/**
 * ScanNetwork.php
 *
 * Scan networks for hosts and add them to LibreNMS.
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

namespace App\Console\Commands;

use App\Actions\Device\AddFoundDeviceResult;
use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Scan\NetworkScanner;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Util\Binaries;
use LibreNMS\Util\IP;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ScanNetwork extends LnmsCommand
{
    protected $name = 'scan:network';

    /** @var array<string, int> results by outcome name */
    private array $counts = [];

    public function __construct()
    {
        parent::__construct();

        $this->setAliases(['scan']);

        $this->addArgument('network', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
        $this->addOption('threads', 't', InputOption::VALUE_REQUIRED, default: 32);
        $this->addOption('poller-group', 'g', InputOption::VALUE_REQUIRED);
        $this->addOption('dns-only', 'o', InputOption::VALUE_NONE);
        $this->addOption('legend', 'l', InputOption::VALUE_NONE);
        $this->addOption('ping-fallback', 'b', InputOption::VALUE_NONE);
        $this->addOption('ping-only', null, InputOption::VALUE_NONE);

        // compatibility with snmp-scan.py
        $this->addOption('ping', 'P', InputOption::VALUE_NONE);
        $this->addOption('range', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
    }

    public function handle(NetworkScanner $scanner, Binaries $binaries): int
    {
        $startTime = microtime(true);

        $networks = $this->networks();
        if ($networks === []) {
            $this->error(__('commands.scan:network.errors.no_network'));

            return 1;
        }

        $parsed = [];
        foreach ($networks as $network) {
            $ip = IP::parse($network, true);
            if ($ip === null) {
                $this->error(__('commands.scan:network.errors.invalid_network', ['network' => $network]));

                return 2;
            }
            $parsed[] = $ip;
        }

        if ($this->option('ping')) {
            $this->warn(__('commands.scan:network.errors.ping_deprecated'));
        }

        $options = ScanOptions::fromConfig(
            concurrency: (int) $this->option('threads'),
            excludedNetworks: $this->excludedNetworks(),
            pollerGroup: $this->pollerGroup(),
            dnsOnly: (bool) $this->option('dns-only'),
            pingFallback: $this->option('ping-fallback') || $this->option('ping'),
            pingOnly: (bool) $this->option('ping-only'),
        );

        foreach ($this->requiredBinaries($options) as $binary) {
            if ($binaries->find($binary) === null) {
                $this->error(__('commands.scan:network.errors.missing_binary', ['binary' => $binary]));

                return 3;
            }
        }

        if (! $options->pingOnly && $options->snmpConfigs === []) {
            $this->warn(__('commands.scan:network.errors.no_snmp_credentials'));
        }

        if ($this->option('legend') && ! $this->output->isVerbose()) {
            $this->printLegend();
        }

        $this->line(__('commands.scan:network.scanning'));
        $this->counts = array_fill_keys(array_column(DeviceAddOutcome::cases(), 'name'), 0);

        $scanner->scan($parsed, $options, $this->printResult(...));

        if (! $this->output->isVerbose()) {
            $this->newLine(2);
        }
        $this->printSummary(microtime(true) - $startTime);

        return 0;
    }

    private function printResult(string $ip, AddFoundDeviceResult $result): void
    {
        $this->counts[$result->outcome->name]++;

        if (! $this->output->isVerbose()) {
            $this->output->write($result->outcome->symbol());

            return;
        }

        $this->line("<options=bold>$ip</> " . $result->outcome->description());
        if ($result->message !== '') {
            $this->line($result->message);
        }
    }

    private function printSummary(float $runtime): void
    {
        $count = fn (DeviceAddOutcome $outcome): int => $this->counts[$outcome->name];

        $this->line(__('commands.scan:network.summary', [
            'count' => array_sum($this->counts) - $count(DeviceAddOutcome::Excluded),
            'known' => $count(DeviceAddOutcome::Known),
            'added' => $count(DeviceAddOutcome::Added),
            'failed' => $count(DeviceAddOutcome::Unreachable) + $count(DeviceAddOutcome::Error),
        ]));

        foreach ([DeviceAddOutcome::Excluded, DeviceAddOutcome::NoDns, DeviceAddOutcome::Error] as $outcome) {
            if ($count($outcome) > 0) {
                $this->line(trans_choice('commands.scan:network.summary_' . $outcome->name, $count($outcome)));
            }
        }

        $this->line(__('commands.scan:network.runtime', ['seconds' => sprintf('%.2f', $runtime)]));
    }

    private function printLegend(): void
    {
        $this->line(__('commands.scan:network.legend'));
        foreach (DeviceAddOutcome::cases() as $outcome) {
            $this->line($outcome->symbol() . '  ' . $outcome->description());
        }
        $this->newLine();
    }

    /**
     * The commands the scan runs.
     *
     * @return string[]
     */
    private function requiredBinaries(ScanOptions $options): array
    {
        return array_keys(array_filter([
            'fping' => $options->icmpEnabled,
            'snmpget' => $options->snmpConfigs !== [],
        ]));
    }

    /**
     * The networks given on the command line, or the nets setting.
     *
     * @return string[]
     */
    private function networks(): array
    {
        $networks = [...(array) $this->argument('network'), ...(array) $this->option('range')];

        return $networks ?: (array) LibrenmsConfig::get('nets', []);
    }

    /**
     * @return string[]
     */
    private function excludedNetworks(): array
    {
        $excluded = [];
        foreach ((array) LibrenmsConfig::get('autodiscovery.nets-exclude', []) as $network) {
            if (IP::parse($network, true) === null) {
                $this->warn(__('commands.scan:network.errors.invalid_excluded_network', ['network' => $network]));
            } else {
                $excluded[] = $network;
            }
        }

        return $excluded;
    }

    /**
     * The given poller group, or the first poller group of this poller.
     */
    private function pollerGroup(): int
    {
        return (int) ($this->option('poller-group')
            ?? explode(',', (string) LibrenmsConfig::get('distributed_poller_group', '0'))[0]);
    }
}
