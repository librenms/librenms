<?php

/**
 * ScanHost.php
 *
 * Internal worker for scan:network, adds a single scanned host.
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
use App\Jobs\AddScannedHost;
use Illuminate\Support\Facades\Bus;
use LibreNMS\Enum\DeviceAddOutcome;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ScanHost extends LnmsCommand
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'scan:host';

    /**
     * Hide command from general CLI listing.
     */
    protected $hidden = true;

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('ip', InputArgument::REQUIRED, '');
        $this->addOption('poller-group', 'g', InputOption::VALUE_REQUIRED, '');
        $this->addOption('dns-only', 'o', InputOption::VALUE_NONE, '');
        $this->addOption('ping-fallback', 'b', InputOption::VALUE_NONE, '');
        $this->addOption('ping-only', 'P', InputOption::VALUE_NONE, '');
    }

    public function handle(): int
    {
        $job = new AddScannedHost(
            ip: (string) $this->argument('ip'),
            pollerGroup: (int) $this->option('poller-group'),
            dnsOnly: (bool) $this->option('dns-only'),
            pingFallback: (bool) $this->option('ping-fallback'),
            pingOnly: (bool) $this->option('ping-only'),
        );

        // run in this process to get the outcome, scan:network runs many of these in parallel
        $outcome = Bus::dispatchNow($job);

        return $outcome instanceof DeviceAddOutcome ? $outcome->value : DeviceAddOutcome::Error->value;
    }
}
