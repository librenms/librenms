<?php

/**
 * DeviceDiscover.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\ProcessesDevices;
use App\Console\LnmsCommand;
use App\Events\DeviceDiscovered;
use App\Jobs\DiscoverDevice;
use App\PerDeviceProcess;
use App\Polling\Measure\MeasurementManager;
use Illuminate\Database\QueryException;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Util\ModuleList;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeviceDiscover extends LnmsCommand
{
    use ProcessesDevices;

    protected $name = 'device:discover';
    protected ProcessType $processType = ProcessType::Discovery;

    public function __construct()
    {
        parent::__construct();
        $this->setAliases(['poller:discovery']); // TODO remove
        $this->addArgument('device spec', InputArgument::REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
        $this->addOption('os', null, InputOption::VALUE_REQUIRED);
        $this->addOption('type', null, InputOption::VALUE_REQUIRED);
    }

    public function handle(MeasurementManager $measurements): int
    {
        try {
            $this->handleDebug();

            $processor = new PerDeviceProcess(
                $this->processType,
                $this->argument('device spec'),
                DiscoverDevice::class,
                DeviceDiscovered::class,
                ModuleList::fromUserOverrides($this->option('modules')),
                $this->option('os'),
                $this->option('type')
            );

            $this->line(__('commands.device:discover.starting'));
            $this->newLine();

            $processor->run();

            return $processor->processResults($measurements, $this->getOutput());
        } catch (QueryException $e) {
            return $this->handleQueryException($e);
        }
    }
}
