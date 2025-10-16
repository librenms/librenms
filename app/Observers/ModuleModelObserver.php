<?php

/*
 * ModuleModelObserver.php
 *
 * Displays +,-,U,. while running discovery and adding,deleting,updating, and doing nothing.
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Observers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Debug;
use Psr\Log\LoggerInterface;

class ModuleModelObserver
{
    private LoggerInterface $logger;

    public function __construct(
        ?Logger $logger = null,
    ) {
        $this->logger = $logger ?? Log::channel('stdout');
    }

    /**
     * Install observers to output +, -, U for models being created, deleted, and updated
     *
     * @param  string|Eloquent  $model  The model name including namespace
     */
    public static function observe($model, string $name = ''): void
    {
        static $observed_models = []; // keep track of observed models so we don't duplicate output
        $class = ltrim($model, '\\');

        if ($name) {
            Log::channel('stdout')->info(ucwords($name) . ': ', ['nlb' => true]);
        }

        if (! in_array($class, $observed_models)) {
            $model::observe(new static());
            $observed_models[] = $class;
        }
    }

    public static function done(): void
    {
        Log::channel('stdout')->info(PHP_EOL, ['nlb' => true]);
    }

    /**
     * @param  Eloquent  $model
     */
    public function saving($model): void
    {
        if (! $model->isDirty()) {
            $this->logger->info('.', ['nlb' => true]);
        }
    }

    /**
     * @param  Eloquent  $model
     */
    public function updated($model): void
    {
        if (Debug::isEnabled()) {
            $this->logger->debug('Updated data:   ' . var_export($model->getDirty(), true));
        } else {
            $this->logger->info('U', ['nlb' => true]);
        }
    }

    /**
     * @param  Eloquent  $model
     */
    public function restored($model): void
    {
        if (Debug::isEnabled()) {
            $this->logger->debug('Restored data:   ' . var_export($model->getDirty(), true));
        } else {
            $this->logger->info('R', ['nlb' => true]);
        }
    }

    /**
     * @param  Eloquent  $model
     */
    public function created($model): void
    {
        $this->logger->info('+', ['nlb' => true]);
    }

    /**
     * @param  Eloquent  $model
     */
    public function deleted($model): void
    {
        $this->logger->info('-', ['nlb' => true]);
    }
}
