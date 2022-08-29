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

        $this->metrics = $service_check->getMetrics($output_matches['metrics'] ?? '');

        if (empty($this->metrics)) {
            \Log::debug('Perf Data - None.');

            return;
        }

        foreach ($this->metrics as $ds => $metric) {
            \Log::debug('Perf Data - DS: ' . $ds . ', Value: ' . $metric['value'] . ', UOM: ' . $metric['uom']);
        }
    }
}
