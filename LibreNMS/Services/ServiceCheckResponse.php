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

use LibreNMS\Interfaces\ServiceCheck;

class ServiceCheckResponse
{
    /** @var array The metrics parsed from the response {ds: {value: <value>, uom: <uom>}} */
    public $metrics = [];
    /** @var int The result of the check */
    public $result;
    /** @var string The textual response of the check */
    public $message;
    /** @var string The command line ran */
    public $commandLine;

    public function __construct(string $output, int $return, ServiceCheck $service_check, string $commandLine)
    {
        $this->commandLine = $commandLine;
        $this->result = $return;

        // Split out the response and the performance data.
        preg_match('/^(?<response>.*?)(\|(?<metrics>[^|]*))?$/s', $output, $output_matches);
        $this->message = $output_matches['response'];

        // Split each performance metric and Loop through the perf string extracting our metric data
        foreach (explode(' ', trim($output_matches['metrics'] ?? '')) as $metric) {
            // Separate the DS and value: DS=value
            // This regex checks for valid UOM's to be used for graphing https://nagios-plugins.org/doc/guidelines.html#AEN200
            if (preg_match('/^(?<ds>[^=]+)=(?<value>[\d.-]+)(?<uom>us|ms|s|KB|MB|GB|TB|c|%|B)/', $metric, $metric_matches)) {
                $this->metrics[$metric_matches['ds']] = [
                    'value' => $metric_matches['value'],
                    'uom' => $metric_matches['uom'],
                    'storage' => $service_check->getStorageType($metric_matches['ds'], $metric_matches['uom']),
                ];
                \Log::debug('Perf Data - DS: ' . $metric_matches['ds'] . ', Value: ' . $metric_matches['value'] . ', UOM: ' . $metric_matches['uom']);
            } else {
                // No DS. Don't add an entry to the array.
                \Log::debug('Perf Data - None.');
            }
        }
    }
}
