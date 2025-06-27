<?php

/**
 * InfluxDBv2.php
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
 * @copyright  2024 Tony Murray
 * @copyright  2024 Ruben van Komen <https://github.com/walkablenormal>
 * @author     Ruben van Komen <rubenvankomen@gmail.com>
 */

namespace LibreNMS\Data\Store;

use App\Facades\LibrenmsConfig;
use App\Polling\Measure\Measurement;
use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;
use InfluxDB2\WriteType;
use Log;

class InfluxDBv2 extends BaseDatastore
{
    private $client;

    public function __construct()
    {
        parent::__construct();

        try {
            $host = LibrenmsConfig::get('influxdbv2.host', 'localhost');
            $transport = LibrenmsConfig::get('influxdbv2.transport', 'http');
            $port = LibrenmsConfig::get('influxdbv2.port', 8086);
            $bucket = LibrenmsConfig::get('influxdbv2.bucket', 'librenms');
            $organization = LibrenmsConfig::get('influxdbv2.organization', '');
            $allow_redirects = LibrenmsConfig::get('influxdbv2.allow_redirects', true);
            $token = LibrenmsConfig::get('influxdbv2.token', '');
            $debug = LibrenmsConfig::get('influxdbv2.debug', false);
            $log_file = LibrenmsConfig::get('influxdbv2.log_file', LibrenmsConfig::get('log_file'));
            $timeout = LibrenmsConfig::get('influxdbv2.timeout', 1);
            $verify = LibrenmsConfig::get('influxdbv2.verify', false);
            $batch_size = LibrenmsConfig::get('influxdbv2.batch_size', 1000);
            $max_retry = LibrenmsConfig::get('influxdbv2.max_retry', 3);

            // The "connection: close" is to avoid a high quantity of TIME_WAIT
            $guzzleOptions = [
                'timeout' => $timeout,
                'verify' => $verify,
                'headers' => [
                    'Connection' => 'close',
                ],
            ];
            $guzzleClient = new \GuzzleHttp\Client($guzzleOptions);

            $client = new Client([
                'url' => $transport . '://' . $host . ':' . $port,
                'token' => $token,
                'bucket' => $bucket,
                'org' => $organization,
                'precision' => WritePrecision::S,
                'allow_redirects' => $allow_redirects,
                'debug' => $debug,
                'logFile' => $log_file,
                'httpClient' => $guzzleClient,
            ]);

            $this->client = $client->createWriteApi([
                'writeType' => WriteType::BATCHING,
                'batchSize' => $batch_size,
                'maxRetries' => $max_retry,
            ]);
        } catch (\InfluxDB2\ApiException $e) {
            Log::error('InfluxDBv2 (__construct) API Exception: ' . $e->getMessage());
        }
    }

    public function terminate(): void
    {
        try {
            $this->client->close();
        } catch (\InfluxDB2\ApiException $e) {
            Log::error('InfluxDBv2 (__destruct) API Exception: ' . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'InfluxDBv2';
    }

    public static function isEnabled(): bool
    {
        return LibrenmsConfig::get('influxdbv2.enable', false);
    }

    /**
     * @inheritDoc
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void
    {
        $device = $this->getDevice($meta);
        $excluded_groups = LibrenmsConfig::get('influxdbv2.groups-exclude');

        if (! empty($excluded_groups)) {
            $device_groups = $device->groups;
            foreach ($device_groups as $group) {
                // The group name will always be parsed as lowercase, even when uppercase in the GUI.
                if (in_array(strtoupper($group->name), array_map('strtoupper', $excluded_groups))) {
                    Log::warning('Skipped parsing to InfluxDBv2, device is in group: ' . $group->name);

                    return;
                }
            }
        }

        $stat = Measurement::start('write');
        $tmp_fields = [];
        $tmp_tags['hostname'] = $device->hostname;
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

        if (LibrenmsConfig::get('influxdbv2.debug') === true) {
            Log::debug('InfluxDB data: ', [
                'measurement' => $measurement,
                'tags' => $tmp_tags,
                'fields' => $tmp_fields,
            ]);
        }

        try {
            // Construct data points using the InfluxDB2\Point class
            $point = Point::measurement($measurement)
              ->addTag('hostname', $device->hostname)
              ->time(microtime(true)); // Assuming you want to use the current time

            // Write the data points to the database using the WriteApi instance
            foreach ($tmp_fields as $field => $value) {
                $point->addField($field, $value);
            }

            // Sort tags alphabetically for performance
            ksort($tmp_tags, SORT_STRING);

            // Adding tags from $tmpTags array
            foreach ($tmp_tags as $tag => $value) {
                $point->addTag($tag, $value);
            }

            // Write the point to the database
            $this->client->write($point);

            $this->recordStatistic($stat->end());
        } catch (\InfluxDB2\ApiException $e) {
            Log::error('InfluxDBv2 (put) API Exception: ' . $e->getMessage());
        }
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
