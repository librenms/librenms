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

use App\Models\Device;
use App\Models\Service;
use LibreNMS\Config;
use LibreNMS\Data\Store\Rrd;

class DefaultServiceCheck implements \LibreNMS\Interfaces\ServiceCheck
{
    /** @var \App\Models\Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @inheritDoc
     */
    public function buildCommand(Device $device): array
    {
        $command = [
            Config::get('nagios_plugins') . '/check_' . $this->service->service_type,
            '-H',
            $this->service->service_ip ?: $device->overwrite_ip ?: $device->hostname,
        ];
        return $this->appendParameters($command);
    }

    public function serviceDataSets(): array
    {
        return $this->service->service_ds;
    }

    public function graphRrdCommands(string $rrd_filename, string $ds): string
    {
        $tint = preg_match('/loss/i', $ds) ? 'pinks' : 'blues';
        $color_avg = Config::get("graph_colours.$tint.2");
        $color_max = Config::get("graph_colours.$tint.0");

        $rrd_additions = ' DEF:DS=' . $rrd_filename . ':' . $ds . ':AVERAGE ';
        $rrd_additions .= ' DEF:DS_MAX=' . $rrd_filename . ':' . $ds . ':MAX ';
        $rrd_additions .= ' AREA:DS_MAX#' . $color_max . ':';
        $rrd_additions .= ' AREA:DS#' . $color_avg . ":'" . Rrd::fixedSafeDescr(ucfirst($ds) . ' (' . ($this->serviceDataSets()[$ds] ?? '') . ')', 15) . "' ";
        $rrd_additions .= ' GPRINT:DS:LAST:%5.2lf%s ';
        $rrd_additions .= ' GPRINT:DS:AVERAGE:%5.2lf%s ';
        $rrd_additions .= ' GPRINT:DS_MAX:MAX:%5.2lf%s\\l ';

        return $rrd_additions;
    }

    /**
     * Get the available check parameters.
     *
     * @return \LibreNMS\Services\CheckParameter[]
     */
    public function availableParameters(): array
    {
        $parser = new HelpParser();

        return array_values($parser->parse('check_' . $this->service->service_type));
    }

    /**
     * Merge either modern (array) or legacy (string) parameters into the command
     *
     * @param  array  $command
     * @return array
     */
    protected function appendParameters(array $command): array
    {
        if (empty($this->service->service_param)) {
            return $command;
        }

        if (is_array($this->service->service_param)) {
            foreach ($this->service->service_param as $flag => $value) {
                $command[] = $flag;

                if ($value) {
                    $command[] = $value;
                }
            }

            return $command;
        }

        return array_merge($command, $this->parseLegacyParams());
    }

    protected function parseLegacyParams(): array
    {
        $parts = preg_split('~(?:\'[^\']*\'|"[^"]*")(*SKIP)(*F)|\h+~', trim($this->service->service_param));
        return array_map(function ($part) {
            return preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $part);
        }, $parts);
    }
}
