<?php

/**
 * ScanNetwork.php
 *
 * Scan networks for SNMP hosts and add them to LibreNMS.
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
use LibreNMS\Util\IP;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ScanNetwork extends LnmsCommand
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'scan:network';

    public function __construct()
    {
        parent::__construct();

        $this->setAliases(['scan']);

        $this->addArgument('network', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, '');
        $this->addOption('threads', 't', InputOption::VALUE_REQUIRED, '', 32);
        $this->addOption('poller-group', 'g', InputOption::VALUE_REQUIRED, '');
        $this->addOption('group', null, InputOption::VALUE_REQUIRED, '');
        $this->addOption('dns-only', 'o', InputOption::VALUE_NONE, '');
        $this->addOption('legend', 'l', InputOption::VALUE_NONE, '');
        $this->addOption('ping-fallback', 'b', InputOption::VALUE_NONE, '');
        $this->addOption('ping-only', 'P', InputOption::VALUE_NONE, '');

        // compatibility arguments
        $this->addOption('ping', null, InputOption::VALUE_NONE, '');
        $this->addOption('r', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, '');
    }

    public function handle(NetworkScanner $scanner): int
    {
        $startTime = microtime(true);

        $networks = $this->networks();
        if ($networks === []) {
            $this->error(__('commands.scan:network.errors.no_network'));

            return 1;
        }

        $parsedNetworks = [];
        foreach ($networks as $network) {
            $parsed = IP::parse($network, true);
            if ($parsed === null) {
                $this->error(__('commands.scan:network.errors.invalid_network', ['network' => $network]));

                return 2;
            }
            $parsedNetworks[] = $parsed;
        }

        $pingOnly = (bool) $this->option('ping-only');
        $options = ScanOptions::fromConfig(
            pollerGroup: $this->pollerGroup(),
            dnsOnly: (bool) $this->option('dns-only'),
            pingFallback: $this->option('ping-fallback') || $this->option('ping'),
            pingOnly: $pingOnly,
        );

        if (! $pingOnly && $options->snmpConfigs === []) {
            $this->warn(__('commands.scan:network.errors.no_snmp_credentials'));
        }

        if ($this->option('legend') && ! $this->output->isVerbose()) {
            $this->printLegend();
        }

        $this->line(__('commands.scan:network.scanning'));

        $counts = array_fill_keys(array_map(fn (DeviceAddOutcome $outcome) => $outcome->name, DeviceAddOutcome::cases()), 0);
        $record = function (string $ip, AddFoundDeviceResult $result) use (&$counts): void {
            $counts[$result->outcome->name]++;

            if ($this->output->isVerbose()) {
                $this->line("<options=bold>$ip</> " . $result->outcome->description());
                if ($result->message !== '') {
                    $this->line($result->message);
                }
            } else {
                $this->output->write($result->outcome->symbol());
            }
        };

        $ips = [];
        $excludedNetworks = $this->excludedNetworks();
        foreach ($parsedNetworks as $network) {
            foreach ($network as $ip) {
                if (IP::parse($ip)->inNetworks($excludedNetworks)) {
                    $record($ip, new AddFoundDeviceResult(DeviceAddOutcome::Excluded));
                } else {
                    $ips[] = $ip;
                }
            }
        }

        $scanner->scan($ips, $options, max((int) $this->option('threads'), 1), $record);

        if (! $this->output->isVerbose()) {
            $this->newLine(2);
        }

        $this->line(__('commands.scan:network.summary', [
            'count' => array_sum($counts),
            'known' => $counts[DeviceAddOutcome::Known->name],
            'added' => $counts[DeviceAddOutcome::Added->name],
            'failed' => $counts[DeviceAddOutcome::Unreachable->name] + $counts[DeviceAddOutcome::Error->name],
        ]));
        foreach ([DeviceAddOutcome::Excluded, DeviceAddOutcome::NoDns, DeviceAddOutcome::Error] as $outcome) {
            if ($counts[$outcome->name] > 0) {
                $this->line(trans_choice('commands.scan:network.summary_' . $outcome->name, $counts[$outcome->name]));
            }
        }
        $this->line(__('commands.scan:network.runtime', ['seconds' => sprintf('%.2f', microtime(true) - $startTime)]));

        return 0;
    }

    /**
     * The networks given on the command line, or the nets setting.
     *
     * @return string[]
     */
    private function networks(): array
    {
        $networks = [...(array) $this->argument('network'), ...(array) $this->option('r')];

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
        $pollerGroup = $this->option('poller-group') ?? $this->option('group')
            ?? explode(',', (string) LibrenmsConfig::get('distributed_poller_group', '0'))[0];

        return (int) $pollerGroup;
    }

    private function printLegend(): void
    {
        $this->line(__('commands.scan:network.legend'));
        foreach (DeviceAddOutcome::cases() as $outcome) {
            $this->line($outcome->symbol() . '  ' . $outcome->description());
        }
        $this->newLine();
    }
}
