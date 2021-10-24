<?php
/**
 * MeasurementManager.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Polling\Measure;

use DB;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Collection;
use Log;

class MeasurementManager
{
    const SNMP_COLOR = "\e[0;36m";
    const DB_COLOR = "\e[1;33m";
    const DATASTORE_COLOR = "\e[0;32m";
    const NO_COLOR = "\e[0m";

    /**
     * @var \Illuminate\Support\Collection<MeasurementCollection>
     */
    private static $categories;

    public function __construct()
    {
        if (self::$categories === null) {
            self::$categories = new Collection;
            self::$categories->put('snmp', new MeasurementCollection());
            self::$categories->put('db', new MeasurementCollection());
        }
    }

    /**
     * Register DB listener to record sql query stats
     */
    public function listenDb(): void
    {
        DB::listen(function (QueryExecuted $event) {
            $type = strtolower(substr($event->sql, 0, strpos($event->sql, ' ')));
            $this->recordDb(Measurement::make($type, $event->time ? $event->time / 100 : 0));
        });
    }

    /**
     * Update statistics for the given category
     */
    public function record(string $category, Measurement $measurement): void
    {
        $this->getCategory($category)->record($measurement);
    }

    /**
     * Update statistics for db operations
     */
    public function recordDb(Measurement $measurement): void
    {
        $this->record('db', $measurement);
    }

    /**
     * Print out the stats totals since the last checkpoint
     */
    public function printChangedStats(): void
    {
        $dsStats = app('Datastore')->getStats()->map(function (MeasurementCollection $stats, $datastore) {
            return sprintf('%s%s%s: [%d/%.2fs]', self::DATASTORE_COLOR, $datastore, self::NO_COLOR, $stats->getCountDiff(), $stats->getDurationDiff());
        });

        Log::info(sprintf(
            '>> %sSNMP%s: [%d/%.2fs] %sMySQL%s: [%d/%.2fs] %s',
            self::SNMP_COLOR,
            self::NO_COLOR,
            $this->getCategory('snmp')->getCountDiff(),
            $this->getCategory('snmp')->getDurationDiff(),
            self::DB_COLOR,
            self::NO_COLOR,
            $this->getCategory('db')->getCountDiff(),
            $this->getCategory('db')->getDurationDiff(),
            $dsStats->implode(' ')
        ));

        $this->checkpoint();
    }

    /**
     * Make a new checkpoint so to compare against
     */
    public function checkpoint(): void
    {
        self::$categories->each->checkpoint();
        app('Datastore')->getStats()->each->checkpoint();
    }

    /**
     * Record a measurement for snmp
     */
    public function recordSnmp(Measurement $measurement): void
    {
        $this->record('snmp', $measurement);
    }

    /**
     * Print global stat arrays
     */
    public function printStats(): void
    {
        $this->printSummary('SNMP', $this->getCategory('snmp'), self::SNMP_COLOR);
        $this->printSummary('SQL', $this->getCategory('db'), self::DB_COLOR);

        app('Datastore')->getStats()->each(function (MeasurementCollection $stats, string $datastore) {
            $this->printSummary($datastore, $stats, self::DATASTORE_COLOR);
        });
    }

    public function getCategory(string $category): MeasurementCollection
    {
        if (! self::$categories->has($category)) {
            self::$categories->put($category, new MeasurementCollection());
        }

        return self::$categories->get($category);
    }

    public function printSummary(string $name, MeasurementCollection $collection, string $color = ''): void
    {
        $summaries = $collection->map(function (MeasurementSummary $stat) {
            return sprintf('%s[%d/%.2fs]', ucfirst($stat->getType()), $stat->getCount(), $stat->getDuration());
        });

        Log::info(sprintf('%s%s%s [%d/%.2fs]: %s',
            $color,
            $name,
            $color ? self::NO_COLOR : '',
            $collection->getTotalCount(),
            $collection->getTotalDuration(),
            $summaries->implode(' ')
        ));
    }
}
