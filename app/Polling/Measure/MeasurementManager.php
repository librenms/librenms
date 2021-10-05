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

use Illuminate\Database\Events\QueryExecuted;

class MeasurementManager
{
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
        \DB::listen(function (QueryExecuted $event) {
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
            '>> SNMP: [%d/%.2fs] MySQL: [%d/%.2fs]',
            self::$snmp->getCountDiff(),
            self::$snmp->getDurationDiff(),
            self::$db->getCountDiff(),
            self::$db->getDurationDiff()
        );

        foreach (app('Datastore')->getStats() as $datastore => $stats) {
            /** @var \App\Polling\Measure\MeasurementCollection $stats */
            printf(' %s: [%d/%.2fs]', $datastore, $stats->getCountDiff(), $stats->getDurationDiff());
        }

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
        printf(
            "SNMP [%d/%.2fs]: Get[%d/%.2fs] Getnext[%d/%.2fs] Walk[%d/%.2fs]\n",
            self::$snmp->getTotalCount(),
            self::$snmp->getTotalDuration(),
            self::$snmp->getSummary('snmpget')->getCount(),
            self::$snmp->getSummary('snmpget')->getDuration(),
            self::$snmp->getSummary('snmpgetnext')->getCount(),
            self::$snmp->getSummary('snmpgetnext')->getDuration(),
            self::$snmp->getSummary('snmpwalk')->getCount(),
            self::$snmp->getSummary('snmpwalk')->getDuration(),
        );

        printf(
            "MySQL [%d/%.2fs]: Select[%d/%.2fs] Update[%d/%.2fs] Insert[%d/%.2fs] Delete[%d/%.2fs]\n",
            self::$db->getTotalCount(),
            self::$db->getTotalDuration(),
            self::$db->getSummary('select')->getCount(),
            self::$db->getSummary('select')->getDuration(),
            self::$db->getSummary('update')->getCount(),
            self::$db->getSummary('update')->getDuration(),
            self::$db->getSummary('insert')->getCount(),
            self::$db->getSummary('insert')->getDuration(),
            self::$db->getSummary('delete')->getCount(),
            self::$db->getSummary('delete')->getDuration(),
        );

        foreach (app('Datastore')->getStats() as $datastore => $stats) {
            /** @var \App\Polling\Measure\MeasurementCollection $stats */
            printf('%s [%d/%.2fs]:', $datastore, $stats->getTotalCount(), $stats->getTotalDuration());

            foreach ($stats as $stat) {
                /** @var \App\Polling\Measure\MeasurementSummary $stat */
                printf(' %s[%d/%.2fs]', ucfirst($stat->getType()), $stat->getCount(), $stat->getDuration());
            }
            echo PHP_EOL;
        }
    }
}
