<?php

/**
 * VictoriaMetrics.php
 *
 * data store implementation for VictoriaMetrics.
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
 */

namespace LibreNMS\Data\Store;

use App\Facades\LibrenmsConfig;
use App\Polling\Measure\Measurement;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Http;

class VictoriaMetrics extends BaseDatastore
{
    private $client;
    private $base_uri;
    private $import_path;

    private $enabled;
    private $prefix;

    public function __construct()
    {
        parent::__construct();

        $url = rtrim(LibrenmsConfig::get('victoriametrics.url'), '/');
        $this->import_path = '/api/v1/import/prometheus';

        $this->base_uri = $url . $this->import_path;
        $this->client = Http::client()->baseUrl($url);

        $user = LibrenmsConfig::get('victoriametrics.user', '');
        $passwd = LibrenmsConfig::get('victoriametrics.password', '');
        if ($user && $passwd) {
            $this->client = $this->client->withBasicAuth($user, $passwd);
        }

        $this->prefix = LibrenmsConfig::get('victoriametrics.prefix', '');
        if ($this->prefix) {
            $this->prefix = "$this->prefix" . '_';
        }

        $this->enabled = self::isEnabled();
    }

    public function getName(): string
    {
        return 'VictoriaMetrics';
    }

    public static function isEnabled(): bool
    {
        return LibrenmsConfig::get('victoriametrics.enable', false);
    }

    /**
     * Write data in Prometheus text exposition format
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void
    {
        $stat = Measurement::start('put');

        if (! $this->enabled) {
            return;
        }

        $device = $this->getDevice($meta);

        $vals = '';
        $timestamp = time() * 1000; // VictoriaMetrics expects milliseconds

        $commonLabels = [
            'hostname' => $device->hostname,
            'measurement' => $measurement,
        ];

        if (LibrenmsConfig::get('victoriametrics.attach_sysname', false)) {
            $commonLabels['sysName'] = $device->sysName;
        }

        foreach ($tags as $t => $v) {
            if ($v !== null) {
                $labelName = $this->sanitizeforPrometheus($t);
                $labelValue = $this->sanitizeLabelValue($v);
                $commonLabels[$labelName] = $labelValue;
            }
        }

        $labelStr = $this->buildLabelString($commonLabels);

        // Add each field as a separate metric
        foreach ($fields as $k => $v) {
            if ($v !== null) {
                $metricName = $this->prefix . $this->sanitizeforPrometheus($k);
                $vals .= "{$metricName}{$labelStr} {$v} {$timestamp}\n";
            }
        }

        Log::debug('VictoriaMetrics put:', [
            'measurement' => $measurement,
            'tags' => $tags,
            'fields' => $fields,
            'vals' => $vals,
        ]);

        try {
            $result = $this->client->withBody($vals, 'text/plain')
                ->post($this->import_path);

            $this->recordStatistic($stat->end());

            if (! $result->successful()) {
                Log::error('VictoriaMetrics Error: ' . $result->body());
            }
        } catch (ConnectionException $e) {
            Log::error("%RFailed to connect to VictoriaMetrics server $this->base_uri, temporarily disabling.%n", ['color' => true]);
            $this->enabled = false;
        }
    }

    /**
     * Sanitize metric name to conform to Prometheus naming conventions
     */
    private function sanitizeforPrometheus(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_:]/', '_', $name);
        if (preg_match('/^[0-9]/', $name)) {
            $name = '_' . $name;
        }

        return $name;
    }

    /**
     * Sanitize label value - escape backslashes, newlines, and quotes
     */
    private function sanitizeLabelValue(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace("\n", '\\n', $value);
        $value = str_replace('"', '\\"', $value);

        return $value;
    }

    /**
     * Build label string for Prometheus format
     */
    private function buildLabelString(array $labels): string
    {
        if (empty($labels)) {
            return '';
        }

        $labelPairs = [];
        foreach ($labels as $name => $value) {
            $labelPairs[] = $name . '="' . $value . '"';
        }

        return '{' . implode(',', $labelPairs) . '}';
    }
}
