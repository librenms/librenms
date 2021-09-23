<?php
/*
 * InfluxDB2.php
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

namespace LibreNMS\Data\Store;

use App\Data\DataGroup;
use App\Graphing\QueryBuilder;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use InfluxDB2\Client;
use InfluxDB2\Point;
use LibreNMS\Config;
use LibreNMS\Data\Measure\Measurement;
use LibreNMS\Data\SeriesData;
use Log;

class InfluxDB2 extends BaseDatastore
{
    /**
     * @var \InfluxDB2\Client
     */
    private $client;
    /**
     * @var bool
     */
    private $annotationsEnabled;
    /**
     * @var \InfluxDB2\WriteApi
     */
    private $writeApi;
    /**
     * @var \InfluxDB2\QueryApi
     */
    private $queryApi;

    public function __construct(Client $influx)
    {
        parent::__construct();
        $this->client = $influx;
        $this->annotationsEnabled = Config::get('datastore.annotations');
    }

    public static function isEnabled()
    {
        return Config::get('influxdb2.enable', false);
    }

    public function getName()
    {
        return 'InfluxDB2';
    }

    public function put($device, $measurement, $tags, $fields)
    {
        // no legacy data
    }

    public function record(DataGroup $dataGroup)
    {
        $stat = Measurement::start('write');
        $fields = [];

        /** @var \App\Data\DataSet $ds */
        foreach ($dataGroup->getDataSets() as $ds) {
            $fields[$ds->getName()] = $ds->getValue();
        }

        $tags = $this->annotationsEnabled
            ? array_merge($dataGroup->getTags(), $dataGroup->getAnnotations())
            : $dataGroup->getTags();

        try {
            $point = new Point(
                $dataGroup->getName(),
                $tags,
                $fields,
                $dataGroup->getTimestamp(),
            );

            $this->wApi()->write($point);

            Log::debug("[InfluxDB2] " . $point->toLineProtocol());
            $this->recordStatistic($stat->end());
        } catch (\Exception $e) {
            Log::error('InfluxDB2 exception: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }

    public function fetch(QueryBuilder $query): SeriesData
    {
//        dd($query->toFluxQuery());
        /** @var \InfluxDB2\FluxTable $resultSet */
        $resultSet = $this->qApi()->query($query->toQuery());

        try {
            $labels = ['timestamp'];
            $values = [];
            /** @var \InfluxDB2\FluxTable $table */
            foreach ($resultSet as $index => $table) {
                $labels[] = Arr::first($table->records)->getField();

                $field_index = $index + 1;  // timestamp first
                /** @var \InfluxDB2\FluxRecord $record */
                foreach ($table->records as $record) {
                    $timestamp = Carbon::parse($record->getTime())->timestamp; // FIXME make flux return timestamps
                    $values[$timestamp][$field_index] = $record->getValue();
                }
            }

            $output = SeriesData::make($labels);
            foreach ($values as $timestamp => $field_values) {
                $output->appendPoint($timestamp, ...$field_values);
            }

            return $output;
        } catch (\Exception $e) {
            return new SeriesData();
        }
    }

    /**
     * Create a new client
     *
     */
    public static function createFromConfig(): Client
    {
        return new Client([
            'url' => Config::get('influxdb2.url', 'http://localhost:8086'),
            'token' => Config::get('influxdb2.token', ''),
            'bucket' => Config::get('influxdb2.bucket', 'librenms'),
            'org' => Config::get('influxdb2.org', 'librenms'),
            'timeout' => Config::get('influxdb.timeout', 0),
            'verifySSL' => Config::get('influxdb2.verifySSL', false),
            'precision' => \InfluxDB2\Model\WritePrecision::S
        ]);
    }

    private function wApi(): \InfluxDB2\WriteApi
    {
        if ($this->writeApi === null) {
            $this->writeApi = $this->client->createWriteApi();
        }

        return $this->writeApi;
    }

    private function qApi(): \InfluxDB2\QueryApi
    {
        if ($this->queryApi === null) {
            $this->queryApi = $this->client->createQueryApi();
        }

        return $this->queryApi;
    }

    public function wantsRrdTags()
    {
        return false;
    }
}
