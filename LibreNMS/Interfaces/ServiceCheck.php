<?php
/**
 * ServiceCheck.php
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

namespace LibreNMS\Interfaces;

use App\Models\Service;
use Illuminate\Support\Collection;

interface ServiceCheck
{
    public function __construct(Service $service);

    /**
     * Build command for poller to check this service check
     */
    public function buildCommand(): array;

    /**
     * Get data sets to be used for graphing.
     * If you don't want to graph all metrics or perhaps want to add synthetic graphs, you can do so here.
     */
    public function serviceDataSets(): array;

    /**
     * Creates the rrdtool commandline for graphing the given data set.
     * See DefaultServiceCheck for base implementation.
     */
    public function graphRrdCommands(string $ds): string;

    /**
     * Get the available check parameters.
     *
     * @return \Illuminate\Support\Collection<\LibreNMS\Services\CheckParameter>
     */
    public function availableParameters(): Collection;

    /**
     * Mark parameters that have defaults with descriptions
     * This will mark these as optional for users and indicate the defaults.
     */
    public function hasDefaults(): array;

    /**
     * Fill in the default value for the given flag, must exist in the hasDefaults() array
     */
    public function getDefault(string $flag): string;

    /**
     * Get metrics from check, should be an array of metrics keyed by the metric name
     * Each metric array should contain:
     *   value: The value of the metric
     *   uom: the unit of measure. see valid options: https://nagios-plugins.org/doc/guidelines.html#AEN200
     *   storage: The RRD storage type: GAUGE, COUNTER, DERIVE, etc https://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html
     *
     * @param  string  $metric_text
     * @return array
     */
    public function getMetrics(string $metric_text): array;

    /**
     * Get Laravel validation rules for the parameters of this check.
     * Rules will be prefixed by "service_param." and prefer the long param over the short.
     */
    public function getParameterValidationRules(): array;
}
