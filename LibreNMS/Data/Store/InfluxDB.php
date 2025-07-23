<?php

/**
 * InfluxDB.php
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
 * @copyright  2020 Tony Murray
 * @copyright  2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Store;

use App\Facades\LibrenmsConfig;
use App\Polling\Measure\Measurement;
use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Driver\UDP;
use Log;

class InfluxDB extends BaseDatastore
{
    /** @var Database */
    private $connection;
    private $batchPoints = []; // Store points before writing
    private $batchSize = 0; // Number of points to write at once
    private $measurements = []; // List of measurements to write

    public function __construct(Database $influx, $registerShutdown = true)
    {
        parent::__construct();
        $this->connection = $influx;
        $this->batchSize = LibrenmsConfig::get('influxdb.batch_size', 0);

        $measurements = LibrenmsConfig::get('influxdb.measurements', '');
        $this->measurements = $measurements === '' ? [] : explode(',', $measurements);

        // if the database doesn't exist, create it.
        try {
            if (! $influx->exists()) {
                $influx->create();
            }
        } catch (\Exception $e) {
            Log::warning('InfluxDB: Could not create database');
        }

        // Ensure batch is flushed on script exit, unless disabled (for tests)
        if ($registerShutdown) {
            register_shutdown_function([$this, 'flushBatch']);
        }
    }

    public function getName(): string
    {
        return 'InfluxDB';
    }

    public static function isEnabled(): bool
    {
        return LibrenmsConfig::get('influxdb.enable', false);
    }

    /**
     * @inheritDoc
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void
    {
        // Check if this measurement is enabled
        if (! empty($this->measurements) && ! in_array($measurement, $this->measurements)) {
            return;
        }

        $stat = Measurement::start('write');
        $tmp_fields = [];
        $tmp_tags['hostname'] = $this->getDevice($meta)->hostname;
        foreach ($tags as $k => $v) {
            if (empty($v)) {
                $v = '_blank_';
            }
            $tmp_tags[$k] = $v;
        }
        foreach ($fields as $k => $v) {
            if ($k == 'time') {
                $k = 'rtime';
            }

            if (($value = $this->forceType($v)) !== null) {
                $tmp_fields[$k] = $value;
            }
        }

        if (empty($tmp_fields)) {
            Log::warning('All fields empty, skipping update', ['orig_fields' => $fields]);

            return;
        }

        if (LibrenmsConfig::get('influxdb.debug', false) === true) {
            Log::debug('InfluxDB data: ', [
                'measurement' => $measurement,
                'tags' => $tmp_tags,
                'fields' => $tmp_fields,
            ]);
        }

        try {
            // Add timestamp to points as current time in milliseconds
            // This is important for InfluxDB to correctly order and store the data
            // This is especially important for batch writes to ensure data is aggregated correctly
            $timestamp = (int) floor(microtime(true) * 1000); // Convert timestamp to milliseconds

            $this->batchPoints[] = new \InfluxDB\Point(
                $measurement,
                null, // the measurement value
                $tmp_tags,
                $tmp_fields, // optional additional fields,
                $timestamp
            );

            // Flush batch if size limit is reached
            if (count($this->batchPoints) >= $this->batchSize) {
                $this->flushBatch();
            }
            $this->recordStatistic($stat->end());
        } catch (\InfluxDB\Exception $e) {
            Log::error('InfluxDB exception: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }

    /**
     * Flush the batch to InfluxDB
     */
    public function flushBatch()
    {
        // Determine the batch size to use for writing
        // If batchSize is not set (0), write all points at once
        $batchSize = $this->batchSize > 0 ? $this->batchSize : count($this->batchPoints);

        // Continue flushing until all points are written
        while (! empty($this->batchPoints)) {
            // Take up to $batchSize points from the batch for this write
            $pointsToWrite = array_splice($this->batchPoints, 0, $batchSize);

            try {
                $this->connection->writePoints($pointsToWrite, 'ms'); // Added timestamps are in milliseconds
                if (LibrenmsConfig::get('influxdb.debug', false) === true) {
                    Log::debug('Flushed batch of ' . count($pointsToWrite) . ' points to InfluxDB');
                }
            } catch (\InfluxDB\Exception $e) {
                Log::error('InfluxDB batch write failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Create a new client and select the database
     *
     * @return Database
     */
    public static function createFromConfig()
    {
        $host = LibrenmsConfig::get('influxdb.host', 'localhost');
        $transport = LibrenmsConfig::get('influxdb.transport', 'http');
        $port = LibrenmsConfig::get('influxdb.port', 8086);
        $db = LibrenmsConfig::get('influxdb.db', 'librenms');
        $username = LibrenmsConfig::get('influxdb.username', '');
        $password = LibrenmsConfig::get('influxdb.password', '');
        $timeout = LibrenmsConfig::get('influxdb.timeout', 0);
        $verify_ssl = LibrenmsConfig::get('influxdb.verifySSL', false);

        $client = new Client($host, $port, $username, $password, $transport == 'https', $verify_ssl, $timeout, $timeout);

        if ($transport == 'udp') {
            $client->setDriver(new UDP($host, $port));
        }

        // Suppress InfluxDB\Database::create(): Implicitly marking parameter $retentionPolicy as nullable is deprecated
        return @$client->selectDB($db);
    }

    private function forceType($data)
    {
        /*
         * It is not trivial to detect if something is a float or an integer, and
         * therefore may cause breakages on inserts.
         * Just setting every number to a float gets around this, but may introduce
         * inefficiencies.
         */

        if (is_numeric($data)) {
            return floatval($data);
        }

        return $data === 'U' ? null : $data;
    }
}
