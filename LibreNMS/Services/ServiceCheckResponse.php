<?php
/**
 * ServiceCheckResponse.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Services;

use LibreNMS\Data\Store\Rrd;

class ServiceCheckResponse
{
    /** @var array The metrics parsed from the response {ds: {value: <value>, uom: <uom>}}*/
    public $metrics = [];
    /** @var int The result of the check */
    public $result;
    /** @var string The textual response of the check */
    public $message;

    public function __construct(string $output, int $return)
    {
        $this->result = $return;

        // Split out the response and the performance data.
        preg_match('/^(?<response>.*?)(\|(?<metrics>[^|]*))?$/s', $output, $output_matches);
        $this->message = $output_matches['response'];

        // Split each performance metric and Loop through the perf string extracting our metric data
        foreach (explode(' ', $output_matches['metrics'] ?? '') as $metric) {
            // Separate the DS and value: DS=value
            // This regex checks for valid UOM's to be used for graphing https://nagios-plugins.org/doc/guidelines.html#AEN200
            if (preg_match('/^(?<ds>[^=]+)=(?<value>[\d.-]+)(?<uom>us|ms|s|KB|MB|GB|TB|c|%|B)/', $metric, $metric_matches)) {
                $ds = $this->uniqueDsName($metric_matches['ds']);
                $this->metrics[$ds] = ['value' => $metric_matches['value'], 'uom' => $metric_matches['uom']];
                \Log::debug('Perf Data - DS: ' . $ds . ', Value: ' . $metric_matches['value'] . ', UOM: ' . $metric_matches['uom']);
            } else {
                // No DS. Don't add an entry to the array.
                \Log::debug('Perf Data - None.');
            }
        }
    }

    private function uniqueDsName(string $ds): string
    {
        // Normalize ds for rrd : ds-name must be 1 to 19 characters long in the characters [a-zA-Z0-9_]
        // http://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html
        $normalized_ds = Rrd::safeName($ds);

        // if ds_name is longer than 19 characters, only use the first 19
        if (strlen($normalized_ds) > 19) {
            $normalized_ds = substr($normalized_ds, 0, 19);
            \Log::debug($ds . ' exceeded 19 characters, renaming to ' . $normalized_ds);
        }

        if ($ds != $normalized_ds) {
            // ds has changed. check if normalized_ds is already in the array
            if (isset($this->metrics[$normalized_ds])) {
                \Log::debug("$normalized_ds collides with an existing index");

                // Try to generate a unique name
                for ($i = 0; $i < 100; $i++) {
                    $tmp_ds_name = substr($normalized_ds, 0, 19 - strlen("$i")) . $i;
                    if (! isset($this->metrics[$tmp_ds_name])) {
                        \Log::debug("$normalized_ds collides with an existing index");
                        return $tmp_ds_name;
                    }
                }

                \Log::debug('could not generate a unique ds-name for ' . $ds);
            }
        }

        return $normalized_ds;
    }
}
