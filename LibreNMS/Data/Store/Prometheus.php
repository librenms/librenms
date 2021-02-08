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
 * @copyright  2020 Tony Murray
 * @copyright  2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Store;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Data\Measure\Measurement;
use Log;

class Prometheus extends BaseDatastore
{
    private $client;
    private $base_uri;
    private $default_opts;
    private $enabled;

    public function __construct(\GuzzleHttp\Client $client)
    {
        parent::__construct();
        $this->client = $client;

        $url = Config::get('prometheus.url');
        $job = Config::get('prometheus.job', 'librenms');
        $this->base_uri = "$url/metrics/job/$job/instance/";

        $this->default_opts = [
            'headers' => ['Content-Type' => 'text/plain'],
        ];
        if ($proxy = get_proxy()) {
            $this->default_opts['proxy'] = $proxy;
        }

        $this->enabled = self::isEnabled();
    }

    public function getName()
    {
        return 'Prometheus';
    }

    public static function isEnabled()
    {
        return Config::get('prometheus.enable', false);
    }

    public function put($device, $measurement, $tags, $fields)
    {
        $stat = Measurement::start('put');
        // skip if needed
        if (! $this->enabled) {
            return;
        }

        try {
            $vals = '';
            $promtags = '/measurement/' . $measurement;

            foreach ($fields as $k => $v) {
                if ($v !== null) {
                    $vals .= "$k $v\n";
                }
            }

            foreach ($tags as $t => $v) {
                if ($v !== null) {
                    $promtags .= (Str::contains($v, '/') ? "/$t@base64/" . base64_encode($v) : "/$t/$v");
                }
            }
            $options = $this->getDefaultOptions();
            $options['body'] = $vals;

            $promurl = $this->base_uri . $device['hostname'] . $promtags;
            if (Config::get('prometheus.attach_sysname', false)) {
                $promurl .= '/sysName/' . $device['sysName'];
            }
            $promurl = str_replace(' ', '-', $promurl); // Prometheus doesn't handle tags with spaces in url

            Log::debug("Prometheus put $promurl: ", [
                'measurement' => $measurement,
                'tags' => $tags,
                'fields' => $fields,
                'vals' => $vals,
            ]);

            $result = $this->client->request('POST', $promurl, $options);

            $this->recordStatistic($stat->end());

            if ($result->getStatusCode() !== 200) {
                Log::error('Prometheus Error: ' . $result->getReasonPhrase());
            }
        } catch (GuzzleException $e) {
            Log::error('Prometheus Exception: ' . $e->getMessage());
        }
    }

    private function getDefaultOptions()
    {
        return $this->default_opts;
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
