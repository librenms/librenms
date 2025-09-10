<?php

/**
 * Prometheus.php
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
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use LibreNMS\Util\Http;
use Log;

class Prometheus extends BaseDatastore
{
    private $client;
    private $base_uri;

    private $enabled;
    private $prefix;

    public function __construct()
    {
        parent::__construct();

        $url = LibrenmsConfig::get('prometheus.url');
        $job = LibrenmsConfig::get('prometheus.job', 'librenms');
        $this->base_uri = "$url/metrics/job/$job/instance/";

        $this->client = Http::client()->baseUrl($this->base_uri);

        $user = LibrenmsConfig::get('prometheus.user', '');
        $passwd = LibrenmsConfig::get('prometheus.password', '');
        if ($user && $passwd) {
            $this->client = $this->client->withBasicAuth($user, $passwd);
        }

        $this->prefix = LibrenmsConfig::get('prometheus.prefix', '');
        if ($this->prefix) {
            $this->prefix = "$this->prefix" . '_';
        }

        $this->enabled = self::isEnabled();
    }

    public function getName(): string
    {
        return 'Prometheus';
    }

    public static function isEnabled(): bool
    {
        return LibrenmsConfig::get('prometheus.enable', false);
    }

    /**
     * @inheritDoc
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void
    {
        $stat = Measurement::start('put');
        // skip if needed
        if (! $this->enabled) {
            return;
        }

        $vals = '';
        $promtags = '/measurement/' . $measurement;

        foreach ($fields as $k => $v) {
            if ($v !== null) {
                $vals .= $this->prefix . "$k $v\n";
            }
        }

        foreach ($tags as $t => $v) {
            if ($v !== null) {
                $promtags .= (Str::contains($v, '/') ? "/$t@base64/" . base64_encode($v) : "/$t/$v");
            }
        }

        $device = $this->getDevice($meta);
        $promurl = $device->hostname . $promtags;
        if (LibrenmsConfig::get('prometheus.attach_sysname', false)) {
            $promurl .= '/sysName/' . $device->sysName;
        }
        $promurl = str_replace(' ', '-', $promurl); // Prometheus doesn't handle tags with spaces in url

        Log::debug("Prometheus put $promurl: ", [
            'measurement' => $measurement,
            'tags' => $tags,
            'fields' => $fields,
            'vals' => $vals,
        ]);

        try {
            $result = $this->client->withBody($vals, 'text/plain')->post($promurl);

            $this->recordStatistic($stat->end());

            if (! $result->successful()) {
                Log::error('Prometheus Error: ' . $result->body());
            }
        } catch (ConnectionException $e) {
            \Illuminate\Support\Facades\Log::error("%RFailed to connect to Prometheus server $this->base_uri, temporarily disabling.%n", ['color' => true]);
            $this->enabled = false;
        }
    }
}
