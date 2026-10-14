<?php

/**
 * DatastoreServiceProvider.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LibreNMS\Data\Store\Datastore;

class DatastoreServiceProvider extends ServiceProvider
{
    protected $namespace = 'LibreNMS\\Data\\Store\\';
    private const STORES = [
        \LibreNMS\Data\Store\Graphite::class,
        \LibreNMS\Data\Store\InfluxDB::class,
        \LibreNMS\Data\Store\InfluxDBv2::class,
        \LibreNMS\Data\Store\OpenTSDB::class,
        \LibreNMS\Data\Store\Prometheus::class,
        \LibreNMS\Data\Store\Rrd::class,
        \LibreNMS\Data\Store\Kafka::class,
    ];

    public function register(): void
    {
        // set up bindings
        foreach (self::STORES as $store) {
            $this->app->singleton($store);
        }

        // bind the Datastore object
        $this->app->singleton('Datastore', function (Application $app) {
            // only datastores enabled by config
            $stores = array_filter(self::STORES, fn (string $store): bool => $store::isEnabled());

            return new Datastore(array_map($app->make(...), array_values($stores)));
        });

        // additional bindings
        $this->registerInflux();
        $this->registerKafka();
    }

    /**
     * Close the datastores, they are created again from the current config when next used
     */
    public static function reset(Application $app): void
    {
        if ($app->resolved('Datastore')) {
            $app->make('Datastore')->terminate();
        }

        foreach ([...self::STORES, 'Datastore', \InfluxDB\Database::class, \RdKafka\Producer::class] as $abstract) {
            $app->forgetInstance($abstract);
        }
    }

    public function registerInflux()
    {
        $this->app->singleton(\InfluxDB\Database::class, fn ($app) => \LibreNMS\Data\Store\InfluxDB::createFromConfig());
    }

    public function registerKafka()
    {
        $this->app->singleton(\RdKafka\Producer::class, fn ($app) => \LibreNMS\Data\Store\Kafka::getClient());
    }
}
