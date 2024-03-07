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

use App\Polling\Measure\Measurement;
use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;
use LibreNMS\Config;
use Log;

class InfluxDBv2 extends BaseDatastore
{
    public function getName()
    {
        return 'InfluxDBv2';
    }

    public static function isEnabled()
    {
        return Config::get('influxdbv2.enable', false);
    }

    /**
     * Datastore-independent function which should be used for all polled metrics.
     *
     * RRD Tags:
     *   rrd_def     RrdDefinition
     *   rrd_name    array|string: the rrd filename, will be processed with rrd_name()
     *   rrd_oldname array|string: old rrd filename to rename, will be processed with rrd_name()
     *   rrd_step             int: rrd step, defaults to 300
     *
     * @param  array  $device
     * @param  string  $measurement  Name of this measurement
     * @param  array  $tags  tags for the data (or to control rrdtool)
     * @param  array|mixed  $fields  The data to update in an associative array, the order must be consistent with rrd_def,
     *                               single values are allowed and will be paired with $measurement
     */
    public function put($device, $measurement, $tags, $fields)
    {
        $stat = Measurement::start('write');
        $tmp_fields = [];
        $tmp_tags['hostname'] = $device['hostname'];
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

        Log::debug('InfluxDB data: ', [
            'measurement' => $measurement,
            'tags' => $tmp_tags,
            'fields' => $tmp_fields,
        ]);

        // Get a WriteApi instance from the client
        $client = self::createFromConfig();
        $writeApi = $client->createWriteApi();
        try {
            // Construct data points using the InfluxDB2\Point class
            $point = Point::measurement($measurement)
              ->addTag('hostname', $device['hostname'])
              ->time(microtime(true)); // Assuming you want to use the current time

            // Write the data points to the database using the WriteApi instance
            foreach ($tmp_fields as $field => $value) {
                $point->addField($field, $value);
            }

            // Adding tags from $tmpTags array
            foreach ($tmp_tags as $tag => $value) {
                $point->addTag($tag, $value);
            }

            $writeApi->write($point);

            $this->recordStatistic($stat->end());
        } catch (\InfluxDB2\ApiException $e) {
            print_r($e);
            // Handle exceptions
        } finally {
            // Close the WriteApi to free resources
            $writeApi->close();
        }
    }

    public static function createFromConfig()
    {
        $host = Config::get('influxdbv2.host', 'localhost');
        $transport = Config::get('influxdbv2.transport', 'http');
        $port = Config::get('influxdbv2.port', 8086);
        $bucket = Config::get('influxdbv2.bucket', 'librenms');
        $organization = Config::get('influxdbv2.organization', '');
        $allow_redirects = Config::get('influxdbv2.allow_redirects', true);
        $token = Config::get('influxdbv2.token', '');

        $client = new Client([
            'url' => $transport . '://' . $host . ':' . $port,
            'token' => $token,
            'bucket' => $bucket,
            'org' => $organization,
            'precision' => WritePrecision::S,
            'allow_redirects' => $allow_redirects,
            'debug' => true,
        ]);

        return $client;
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

    /**
     * Checks if the datastore wants rrdtags to be sent when issuing put()
     *
     * @return bool
     */
    public function wantsRrdTags()
    {
        return false;
    }
}
