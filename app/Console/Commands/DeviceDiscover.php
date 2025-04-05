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

use App\Console\LnmsCommand;
use App\Events\DeviceDiscovered;
use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\PerDeviceProcess;
use App\Polling\Measure\MeasurementManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Polling\Result;
use LibreNMS\Util\Version;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeviceDiscover extends LnmsCommand
{
    protected $name = 'device:discover';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_REQUIRED);
    }

    public function handle(MeasurementManager $measurements): int
    {
        try {
            if ($this->getOutput()->isVerbose()) {
                Log::debug(Version::get()->header());
                LibrenmsConfig::invalidateAndReload();
            }
            $processor = new PerDeviceProcess(
                ProcessType::discovery,
                $this->argument('device spec'),
                DiscoverDevice::class,
                DeviceDiscovered::class,
                explode(',', $this->option('modules') ?? ''),
            );

            $this->line("Starting discovery run:\n");
            $result = $processor->run();

            return $this->processResults($result, $measurements);
        } catch (QueryException $e) {
            if ($e->getCode() == 2002) {
                $this->error(trans('commands.device:poll.errors.db_connect')); // FIXME

                return 1;
            } elseif ($e->getCode() == 1045) {
                // auth failed, don't need to include the query
                $this->error(trans('commands.device:poll.errors.db_auth', ['error' => $e->getPrevious()->getMessage()])); // FIXME

                return 1;
            }

            $this->error($e->getMessage());

            return 1;
        }
    }

    private function processResults(Result $result, MeasurementManager $measurements): int
    {
        return 0; // TODO
    }
}
