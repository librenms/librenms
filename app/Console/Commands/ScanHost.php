<?php

/**
 * ScanHost.php
 *
 * Internal command to scan and add a single network host.
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
use LibreNMS\Enum\ScanOutcome;
use LibreNMS\Util\Dns;
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

    public function handle(Dns $dns): int
    {
        $ip = (string) $this->argument('ip');
        $hostname = $dns->resolveHostname($ip);

        if ($this->option('dns-only') && ! $hostname) {
            $this->line('DNS not Resolved');

            return ScanOutcome::NoDns->value;
        }

        $addArgs = ['device spec' => $hostname ?: $ip];

        if ($pollerGroup = $this->option('poller-group')) {
            $addArgs['--poller-group'] = $pollerGroup;
        }

        if ($this->option('ping-only')) {
            $addArgs['--ping-only'] = true;
        } elseif ($this->option('ping-fallback')) {
            $addArgs['--ping-fallback'] = true;
        }

        return $this->call('device:add', $addArgs);
    }
}
