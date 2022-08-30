<?php
/*
 * DefaultServiceCheck.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Services;

use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Services;

class DefaultServiceCheck implements \LibreNMS\Interfaces\ServiceCheck
{
    /** @var \App\Models\Service */
    protected $service;
    /** @var string short option to indicate supply service_ip for */
    protected $target_option = '-H';

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @inheritDoc
     */
    public function buildCommand(): array
    {
        return $this->appendParameters([
            Str::finish(Config::get('nagios_plugins'), '/') . 'check_' . $this->service->service_type,
        ]);
    }

    /**
     * Get array of stored datasets for graphing
     */
    public function serviceDataSets(): array
    {
        return $this->service->service_ds ?? [];
    }

    /**
     * Creates the rrdtool commandline for graphing the given data set.
     * See DefaultServiceCheck for base implementation.
     */
    public function graphRrdCommands(string $ds): string
    {
        if (! isset($this->serviceDataSets()[$ds])) {
            return '';
        }

        $rrd_filename = $this->rrdName($ds);
        if (\Rrd::checkRrdExists($rrd_filename)) {
            $title = Rrd::fixedSafeDescr(ucfirst($ds) . ($this->service->service_ds[$ds] ? ' (' . $this->service->service_ds[$ds] . ')' : ''), 15);

            $tint = preg_match('/loss/i', $ds) ? 'pinks' : 'blues';
            $color_avg = Config::get("graph_colours.$tint.2");
            $color_max = Config::get("graph_colours.$tint.0");

            $rrd_additions = ' DEF:DS=' . $rrd_filename . ':value:AVERAGE ';
            $rrd_additions .= ' DEF:DS_MAX=' . $rrd_filename . ':value:MAX ';
            $rrd_additions .= ' AREA:DS_MAX#' . $color_max . ':';
            $rrd_additions .= ' AREA:DS#' . $color_avg . ":'" . $title . "' ";
            $rrd_additions .= ' GPRINT:DS:LAST:%5.2lf%s ';
            $rrd_additions .= ' GPRINT:DS:AVERAGE:%5.2lf%s ';
            $rrd_additions .= ' GPRINT:DS_MAX:MAX:%5.2lf%s\\l ';

            return $rrd_additions;
        }

        return '';
    }

    /**
     * Get the available check parameters.
     *
     * @return \Illuminate\Support\Collection<\LibreNMS\Services\CheckParameter>
     */
    public function availableParameters(): Collection
    {
        $parser = new HelpParser();

        $checkParameters = $parser->parse('check_' . $this->service->service_type);

        // mark the target (service_ip) option if it exists, by default this is -H
        optional($checkParameters->get($this->target_option))->usesTarget();

        // mark defaults as having default
        foreach ($this->hasDefaults() as $option => $text) {
            $checkParameters->get($option)->setHasDefault();
        }

        return $checkParameters;
    }

    /**
     * Mark parameters that have defaults with descriptions
     * This will mark these as optional for users and indicate the defaults.
     */
    public function hasDefaults(): array
    {
        return [
            $this->target_option => trans('service.defaults.hostname'),
        ];
    }

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
    public function getMetrics(string $metric_text): array
    {
        $metrics = [];

        // Split each performance metric and Loop through the perf string extracting our metric data
        foreach (explode(' ', trim($metric_text)) as $metric) {
            // Separate the DS and value: DS=value
            // This regex checks for valid UOM's to be used for graphing https://nagios-plugins.org/doc/guidelines.html#AEN200
            if (preg_match('/^(?<ds>[^=]+)=(?<value>[\d.-]+)(?<uom>us|ms|s|KB|MB|GB|TB|c|%|B)?;/', $metric, $metric_matches)) {
                $uom = $metric_matches['uom'] ?? '';
                $ds = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $metric_matches['ds']); // remove surrounding quotes if found
                $metrics[$ds] = [
                    'value' => $metric_matches['value'],
                    'uom' => $uom,
                    'storage' => $this->getStorageType($ds, $uom),
                ];
            }
        }

        return $metrics;
    }

    /**
     * Get the storage type GAUGE, COUNTER, DERIVE, etc
     * https://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html
     */
    protected function getStorageType(string $ds, string $uom): string
    {
        if (($uom == 'c') && ! (preg_match('/[Uu]ptime/', $ds))) {
            return 'COUNTER';
        }

        return 'GAUGE';
    }

    /**
     * Fill in the default value for the given flag, must exist in the hasDefaults() array
     */
    public function getDefault(string $flag): string
    {
        switch ($flag) {
            case $this->target_option:
                return $this->service->service_ip ?: $this->service->device->overwrite_ip ?: $this->service->device->hostname;
            default:
                return '';
        }
    }

    /**
     * Helper method to generate the rrd file name for a given data set
     */
    protected function rrdName(string $ds): string
    {
        return \Rrd::name($this->service->device->hostname, ['service', $this->service->service_id, $ds]);
    }

    /**
     * Merge either modern (array) or legacy (string) parameters into the command
     */
    private function appendParameters(array $command): array
    {
        $flags = array_keys($this->hasDefaults());

        // service does not have -H (or short_target_option), don't try to set it
        if ($this->service->service_ip === null) {
            if (($key = array_search($this->target_option, $flags)) !== false) {
                unset($flags[$key]);
            }
        }

        $modern = is_array($this->service->service_param);
        if ($modern) {
            $flags = array_merge($flags, array_keys($this->service->service_param));
        }

        foreach ($flags as $flag) {
            $command[] = $flag;

            $value = $this->service->service_param[$flag] ?? $this->getDefault($flag);
            if ($value) {
                $command[] = $value;
            }
        }

        return $modern ? $command : array_merge($command, Services::parseLegacyParams($this->service->service_param));
    }

    public function getParameterValidationRules(): array
    {
        $parameter_rules = [];

        $parameters = $this->availableParameters()->keyBy(function ($parameter) {
            return $parameter->short ?: $parameter->param;
        });

        foreach ($parameters as $parameter) {
            if ($parameter->uses_target) {
                // this option uses target, add service_ip to the rules, but not this parameter
                $parameter_rules['service_ip'] = 'nullable|ip_or_hostname';
                continue;
            }

            $rules = [];
            $param = $parameter->param ?: $parameter->short;

            if ($parameter->required) {
                $rules[] = 'required';
            } elseif ($parameter->inclusive_group) {
                $rules[] = 'required_with:' . implode(',', $parameters->only($parameter->inclusive_group)->map(function (CheckParameter $param) {
                        return 'service_param.' . ($param->param ?: $param->short);
                    })->all());
            }

            if ($parameter->exclusive_group) {
                $rules[] = 'prohibits:' . implode(',', $parameters->only($parameter->exclusive_group)->except($parameter->short ?: $parameter->param)->map(function (CheckParameter $param) {
                        return 'service_param.' . ($param->param ?: $param->short);
                    })->all());
            }

            if ($parameter->value == 'INTEGER') {
                $rules[] = 'integer';
            } elseif ($parameter->value == 'ADDRESS') {
                $rules[] = 'ip_or_hostname';
            } elseif ($parameter->value == 'DOUBLE') {
                $rules[] = 'numeric';
            }

            $parameter_rules["service_param.$param"] = $rules;
        }

        return $parameter_rules;
    }
}
