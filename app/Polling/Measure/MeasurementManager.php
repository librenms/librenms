<?php
/*
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Polling\Measure;

use DB;
use Illuminate\Database\Events\QueryExecuted;

class MeasurementManager
{
    const SNMP_COLOR = "\e[0;36m";
    const DB_COLOR = "\e[1;33m";
    const DATASTORE_COLOR = "\e[0;32m";
    const NO_COLOR = "\e[0m";

    /**
     * @var MeasurementCollection
     */
    private static $snmp;

    /**
     * @var MeasurementCollection
     */
    private static $db;

    public function __construct()
    {
        if (self::$snmp === null) {
            self::$snmp = new MeasurementCollection();
            self::$db = new MeasurementCollection();
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
     * Update statistics for db operations
     */
    public function recordDb(Measurement $measurement): void
    {
        self::$db->record($measurement);
    }

    /**
     * Print out the stats totals since the last checkpoint
     */
    public function printChangedStats(): void
    {
        printf(
            '>> %sSNMP%s: [%d/%.2fs] %sMySQL%s: [%d/%.2fs]',
            self::SNMP_COLOR,
            self::NO_COLOR,
            self::$snmp->getCountDiff(),
            self::$snmp->getDurationDiff(),
            self::DB_COLOR,
            self::NO_COLOR,
            self::$db->getCountDiff(),
            self::$db->getDurationDiff()
        );

        app('Datastore')->getStats()->each(function (MeasurementCollection $stats, $datastore) {
            printf(' %s%s%s: [%d/%.2fs]', self::DATASTORE_COLOR, $datastore, self::NO_COLOR, $stats->getCountDiff(), $stats->getDurationDiff());
        });

        $this->checkpoint();

        echo PHP_EOL;
    }

    /**
     * Make a new checkpoint so to compare against
     */
    public function checkpoint(): void
    {
        self::$snmp->checkpoint();
        self::$db->checkpoint();
        app('Datastore')->getStats()->each->checkpoint();
    }

    /**
     * Record a measurement for snmp
     */
    public function recordSnmp(Measurement $measurement): void
    {
        self::$snmp->record($measurement);
    }

    /**
     * Print global stat arrays
     */
    public function printStats(): void
    {
        $this->printSummary('SNMP', self::$snmp, self::SNMP_COLOR);
        $this->printSummary('SQL', self::$db, self::DB_COLOR);

        app('Datastore')->getStats()->each(function (MeasurementCollection $stats, string $datastore) {
            $this->printSummary($datastore, $stats, self::DATASTORE_COLOR);
        });
    }

    private function printSummary(string $name, MeasurementCollection $collection, string $color = ''): void
    {
        printf('%s%s%s [%d/%.2fs]:', $color, $name, $color ? self::NO_COLOR : '', $collection->getTotalCount(), $collection->getTotalDuration());

        $collection->each(function (MeasurementSummary $stat) {
            printf(' %s[%d/%.2fs]', ucfirst($stat->getType()), $stat->getCount(), $stat->getDuration());
        });

        echo PHP_EOL;
    }
}
