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
use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Support\Logger;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Scan\NetworkScanner;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Util\Binaries;
use LibreNMS\Util\IP;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;
use function Laravel\Prompts\task;

class ScanNetwork extends LnmsCommand
{
    use Colors;

    protected $name = 'scan:network';

    /** draw a live view instead of printing lines */
    private bool $live = false;
    /** @var array<string, int> results by outcome name */
    private array $counts = [];
    /** the number of addresses in the networks */
    private int|float $total = 0;
    /** @var array<string, AddFoundDeviceResult> added devices and errors to print after the progress symbols, by address */
    private array $notable = [];

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
    }

    public function handle(NetworkScanner $scanner, Binaries $binaries): int
    {
        $startTime = microtime(true);

        $networks = $this->networks();
        if ($networks === []) {
            $this->components->error(__('commands.scan:network.errors.no_network'));

            return 1;
        }

        $parsed = [];
        foreach ($networks as $network) {
            $ip = IP::parse($network, true);
            if ($ip === null) {
                $this->components->error(__('commands.scan:network.errors.invalid_network', ['network' => $network]));

                return 2;
            }
            $parsed[] = $ip;
        }

        $options = ScanOptions::fromConfig(
            concurrency: (int) $this->option('threads'),
            excludedNetworks: $this->excludedNetworks(),
            pollerGroup: $this->pollerGroup(),
            dnsOnly: (bool) $this->option('dns-only'),
            pingFallback: (bool) $this->option('ping-fallback'),
            pingOnly: (bool) $this->option('ping-only'),
        );

        foreach ($this->requiredBinaries($options) as $binary) {
            if ($binaries->find($binary) === null) {
                $this->components->error(__('commands.scan:network.errors.missing_binary', ['binary' => $binary]));

                return 3;
            }
        }

        if (! $options->pingOnly && $options->snmpConfigs === []) {
            $this->components->warn(__('commands.scan:network.errors.no_snmp_credentials'));
        }

        $this->counts = array_fill_keys(array_column(DeviceAddOutcome::cases(), 'name'), 0);
        $parsed = $scanner->withoutOverlaps($parsed);
        $this->total = array_sum(array_map(fn (IP $network): int|float => $network->addressCount(), $parsed));
        $label = __('commands.scan:network.scanning', ['networks' => implode(', ', $networks)]);
        $this->live = $this->canDrawLive();

        if ($this->live) {
            intro(__('commands.scan:network.title'));
        }

        if ($this->option('legend')) {
            $this->printLegend();
        }

        if ($this->live) {
            task($label, function (Logger $logger) use ($scanner, $parsed, $options): void {
                $scanner->scan($parsed, $options, fn (string $ip, AddFoundDeviceResult $result) => $this->logResult($logger, $ip, $result));
            }, limit: 5, keepSummary: true, subLabel: $this->progress());
            $this->newLine();
        } else {
            $this->components->info($label);
            $scanner->scan($parsed, $options, $this->printResult(...));
            $this->newLine($this->output->isVerbose() ? 1 : 2);
            foreach ($this->notable as $ip => $result) {
                $this->printDetails($ip, $result);
            }
        }

        $this->printSummary(microtime(true) - $startTime);

        return 0;
    }

    /**
     * Draw a live view of the scan, the details of each result are printed as lines with -v.
     */
    private function canDrawLive(): bool
    {
        return $this->output->isDecorated()
            && ! $this->output->isVerbose()
            && function_exists('pcntl_fork') && function_exists('posix_kill');
    }

    private function logResult(Logger $logger, string $ip, AddFoundDeviceResult $result): void
    {
        $this->counts[$result->outcome->name]++;

        match ($result->outcome) {
            DeviceAddOutcome::Added => $logger->success("{$this->bold($ip)}  $result->message"),
            DeviceAddOutcome::Error => $logger->error("{$this->bold($ip)}  " . strtok($result->message, PHP_EOL)),
            default => $logger->line($this->symbol($result->outcome) . ' ' . str_pad($ip, 15) . '  ' . $result->outcome->description()),
        };

        $logger->subLabel($this->progress());
    }

    /**
     * Print a symbol for each address to show progress, or the details of each address with -v.
     */
    private function printResult(string $ip, AddFoundDeviceResult $result): void
    {
        $this->counts[$result->outcome->name]++;

        if ($this->output->isVerbose()) {
            $this->printDetails($ip, $result);

            return;
        }

        $this->output->write($result->outcome->symbol());

        if (in_array($result->outcome, [DeviceAddOutcome::Added, DeviceAddOutcome::Error])) {
            $this->notable[$ip] = $result;
        }
    }

    private function printDetails(string $ip, AddFoundDeviceResult $result): void
    {
        $this->line($result->outcome->symbol() . " <options=bold>$ip</> " . $result->outcome->description());
        if ($result->message !== '') {
            $this->line('  ' . str_replace(PHP_EOL, PHP_EOL . '  ', $result->message));
        }
    }

    /**
     * How far the scan is and how many devices were added, known or failed so far.
     */
    private function progress(): string
    {
        $done = array_sum($this->counts);

        return implode(' · ', [
            __('commands.scan:network.progress', [
                'done' => number_format($done),
                'total' => number_format($this->total),
                'percent' => floor($done / max($this->total, 1) * 100),
            ]),
            trans_choice('commands.scan:network.progress_added', $this->counts[DeviceAddOutcome::Added->name]),
            trans_choice('commands.scan:network.progress_known', $this->counts[DeviceAddOutcome::Known->name]),
            trans_choice('commands.scan:network.progress_errors', $this->counts[DeviceAddOutcome::Error->name]),
        ]);
    }

    private function printSummary(float $runtime): void
    {
        $rows = [];
        foreach (DeviceAddOutcome::cases() as $outcome) {
            if ($this->counts[$outcome->name] > 0) {
                $rows[] = [$this->symbol($outcome) . '  ' . $outcome->description(), number_format($this->counts[$outcome->name])];
            }
        }

        $headers = [__('commands.scan:network.result'), __('commands.scan:network.addresses')];
        $scanned = trans_choice('commands.scan:network.finished', array_sum($this->counts), [
            'count' => number_format(array_sum($this->counts)),
            'seconds' => sprintf('%.1f', $runtime),
        ]);

        $this->printTable($headers, $rows);
        $this->live ? outro($scanned) : $this->components->info($scanned);
    }

    private function printLegend(): void
    {
        $rows = array_map(fn (DeviceAddOutcome $outcome) => [$this->symbol($outcome), $outcome->description()], DeviceAddOutcome::cases());

        $this->printTable([__('commands.scan:network.symbol'), __('commands.scan:network.legend')], $rows);
    }

    /**
     * @param  string[]  $headers
     * @param  array<int, string[]>  $rows
     */
    private function printTable(array $headers, array $rows): void
    {
        $this->live ? table($headers, $rows) : $this->table($headers, $rows);
    }

    /**
     * The outcome symbol, colored in the live view.
     */
    private function symbol(DeviceAddOutcome $outcome): string
    {
        $symbol = $outcome->symbol();

        if (! $this->live) {
            return $symbol;
        }

        return match ($outcome) {
            DeviceAddOutcome::Added => $this->green($symbol),
            DeviceAddOutcome::Known => $this->cyan($symbol),
            DeviceAddOutcome::NoDns => $this->yellow($symbol),
            DeviceAddOutcome::Error => $this->red($symbol),
            default => $this->gray($symbol),
        };
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
        return (array) $this->argument('network') ?: (array) LibrenmsConfig::get('nets', []);
    }

    /**
     * @return string[]
     */
    private function excludedNetworks(): array
    {
        $excluded = [];
        foreach ((array) LibrenmsConfig::get('autodiscovery.nets-exclude', []) as $network) {
            if (IP::parse($network, true) === null) {
                $this->components->warn(__('commands.scan:network.errors.invalid_excluded_network', ['network' => $network]));
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
