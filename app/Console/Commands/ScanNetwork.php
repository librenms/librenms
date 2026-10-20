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

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\ScanOutcome;
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

    public function handle(): int
    {
        $startTime = microtime(true);

        $networkArgs = (array) $this->argument('network');
        if ($this->option('r')) {
            $networkArgs = array_merge($networkArgs, (array) $this->option('r'));
        }

        $configNets = LibrenmsConfig::get('nets', []);
        if (empty($configNets) && empty($networkArgs)) {
            $this->error(__('commands.scan:network.errors.no_network'));

            return 1;
        }

        $rawNetworks = ! empty($networkArgs) ? $networkArgs : (array) $configNets;
        $networksToScan = [];
        foreach ($rawNetworks as $net) {
            try {
                $network = IP::parse((string) $net);
                if ($network === null) {
                    throw new \UnexpectedValueException();
                }

                $networksToScan[] = $network;
            } catch (\Throwable) {
                $this->error(__('commands.scan:network.errors.invalid_network', ['network' => $net]));

                return 2;
            }
        }

        $excludedNetsConfig = LibrenmsConfig::get('autodiscovery.nets-exclude', []);
        $excludedNets = [];
        foreach ((array) $excludedNetsConfig as $exNet) {
            try {
                $exObj = IP::parse($exNet);
                if ($exObj) {
                    $excludedNets[] = $exNet;
                }
            } catch (\Throwable) {
                $this->error(__('commands.scan:network.errors.invalid_excluded_network', ['network' => $exNet]));
            }
        }

        $pollerGroupArg = $this->option('poller-group') ?? $this->option('group');
        if ($pollerGroupArg !== null) {
            $pollerGroup = (string) $pollerGroupArg;
        } else {
            $distGroup = LibrenmsConfig::get('distributed_poller_group');
            $pollerGroup = $distGroup !== null ? explode(',', (string) $distGroup)[0] : '0';
        }

        $threads = (int) ($this->option('threads') ?: 32);
        $dnsOnly = (bool) $this->option('dns-only');
        $legend = (bool) $this->option('legend');
        $pingFallback = (bool) ($this->option('ping-fallback') || $this->option('ping'));
        $pingOnly = (bool) $this->option('ping-only');

        $verbosity = $this->getOutput()->getVerbosity();

        if ($legend && $verbosity < 64) {
            $this->line("Legend:\n+  Added device\n*  Known device\n-  Failed to add device\n.  Ping failed\n~  Skipped due to no Reverse DNS\nE  Error when checking\n");
        }

        $this->line('Scanning IPs:');

        $stats = [
            'count' => 0,
            ScanOutcome::Added->value => 0,
            ScanOutcome::Unpingable->value => 0,
            ScanOutcome::Known->value => 0,
            ScanOutcome::Failed->value => 0,
            ScanOutcome::Excluded->value => 0,
            ScanOutcome::Terminated->value => 0,
            ScanOutcome::NoDns->value => 0,
            ScanOutcome::Error->value => 0,
        ];

        $allIps = [];
        foreach ($networksToScan as $network) {
            foreach ($network as $ip) {
                if ($this->isIpExcluded($ip, $excludedNets)) {
                    if ($verbosity >= 64) {
                        $this->line("<fg=red>{$ip} excluded by autodiscovery.nets-exclude</>");
                    }
                    $stats[ScanOutcome::Excluded->value]++;

                    continue;
                }

                $allIps[] = $ip;
            }
        }

        $options = ScanOptions::fromConfig(
            pollerGroup: $pollerGroup,
            dnsOnly: $dnsOnly,
            pingFallback: $pingFallback,
            pingOnly: $pingOnly,
        );

        if (! $pingOnly && empty($options->snmpConfigs)) {
            $this->warn(__('commands.scan:network.errors.no_snmp_credentials'));
        }

        $scanner = new NetworkScanner();
        $scanner->scan($allIps, $options, $threads, function (string $ip, ScanOutcome $outcome) use (&$stats, $verbosity): void {
            $stats['count']++;
            $stats[$outcome->value]++;

            if ($verbosity >= 64) {
                $this->line(sprintf('Scanned <options=bold>%s</options=bold> %s', $ip, $outcome->name));
            } else {
                $this->output->write($outcome->symbol());
            }
        });

        if ($verbosity < 64) {
            $this->newLine(2);
        }

        $summary = sprintf(
            'Scanned %d IPs: %d known devices, added %d devices, failed to add %d devices',
            $stats['count'],
            $stats[ScanOutcome::Known->value],
            $stats[ScanOutcome::Added->value],
            $stats[ScanOutcome::Failed->value]
        );

        if ($stats[ScanOutcome::Excluded->value] > 0) {
            $summary .= sprintf(', %d ips excluded by config', $stats[ScanOutcome::Excluded->value]);
        }
        if ($stats[ScanOutcome::NoDns->value] > 0) {
            $summary .= sprintf(', %d ips excluded due to missing reverse DNS record', $stats[ScanOutcome::NoDns->value]);
        }
        if ($stats[ScanOutcome::Error->value] > 0) {
            $summary .= sprintf(', %d errors while checking device (try with -v to see errors)', $stats[ScanOutcome::Error->value]);
        }

        $this->line($summary);
        $this->line(sprintf('Runtime: %.2f seconds', microtime(true) - $startTime));

        return 0;
    }

    /**
     * @param  string[]  $excludedNets
     */
    private function isIpExcluded(string $ip, array $excludedNets): bool
    {
        foreach ($excludedNets as $exNet) {
            try {
                $ipObj = IP::parse($ip);
                if ($ipObj && $ipObj->inNetwork($exNet)) {
                    return true;
                }
            } catch (\Throwable) {
                // Ignore invalid exNet
            }
        }

        return false;
    }
}
