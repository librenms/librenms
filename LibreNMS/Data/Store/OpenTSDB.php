<?php

/**
 * OpenTSDB.php
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
 * @copyright  2017 Yacine Benamsili <https://github.com/yac01/librenms.git>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Store;

use App\Polling\Measure\Measurement;
use Carbon\Carbon;
use LibreNMS\Config;
use Log;

class OpenTSDB extends BaseDatastore
{
    /** @var \Socket\Raw\Socket */
    protected $connection;

    public function __construct(\Socket\Raw\Factory $socketFactory)
    {
        parent::__construct();
        $host = Config::get('opentsdb.host');
        $port = Config::get('opentsdb.port', 2181);
        try {
            if (self::isEnabled() && $host && $port) {
                $this->connection = $socketFactory->createClient("$host:$port");
            }
        } catch (\Socket\Raw\Exception $e) {
            Log::debug('OpenTSDB Error: ' . $e->getMessage());
        }

        if ($this->connection) {
            Log::notice('Connected to OpenTSDB');
        } else {
            Log::error('Connection to OpenTSDB has failed!');
        }
    }

    public function getName(): string
    {
        return 'OpenTSDB';
    }

    /**
     * @inheritDoc
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void
    {
        if (! $this->connection) {
            Log::error("OpenTSDB Error: not connected\n");

            return;
        }

        $flag = Config::get('opentsdb.co');
        $timestamp = Carbon::now()->timestamp;
        $tmp_tags = 'hostname=' . $this->getDevice($meta)->hostname;

        foreach ($tags as $k => $v) {
            $v = str_replace([' ', ',', '='], '_', $v);
            if (! empty($v)) {
                $tmp_tags = $tmp_tags . ' ' . $k . '=' . $v;
            }
        }

        if ($measurement == 'ports') {
            foreach ($fields as $k => $v) {
                $measurement = $k;

                $this->putData('port.' . $measurement, $timestamp, $v, $tmp_tags);
            }
        } else {
            foreach ($fields as $k => $v) {
                $tmp_tags_key = $tmp_tags . ' ' . 'key' . '=' . $k;
                $this->putData($measurement, $timestamp, $v, $tmp_tags_key);
            }
        }
    }

    private function putData($measurement, $timestamp, $value, $tags)
    {
        try {
            $stat = Measurement::start('put');

            $line = sprintf('put net.%s %d %f %s', strtolower($measurement), $timestamp, $value, $tags);
            Log::debug("Sending to OpenTSDB: $line\n");
            $this->connection->write("$line\n"); // send $line into OpenTSDB

            $this->recordStatistic($stat->end());
        } catch (\Socket\Raw\Exception $e) {
            Log::error('OpenTSDB Error: ' . $e->getMessage());
        }
    }

    public static function isEnabled(): bool
    {
        return Config::get('opentsdb.enable', false);
    }
}
