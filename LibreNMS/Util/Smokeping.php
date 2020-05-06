<?php
/**
 * Smokeping.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Config;

class Smokeping
{
    private $device;
    private $files;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public static function make(Device $device)
    {
        return new static($device);
    }

    public function getFiles()
    {
        if (is_null($this->files) && Config::has('smokeping.dir')) {
            $dir = $this->generateFileName();
            if (is_dir($dir)) {
                foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
                    if (stripos($file, '.rrd') !== false) {
                        if (strpos($file, '~') !== false) {
                            list($target, $slave) = explode('~', $this->filenameToHostname($file));
                            $this->files['in'][$target][$slave] = $file;
                            $this->files['out'][$slave][$target] = $file;
                        } else {
                            $target = $this->filenameToHostname($file);
                            $this->files['in'][$target][Config::get('own_hostname')] = $file;
                            $this->files['out'][Config::get('own_hostname')][$target] = $file;
                        }
                    }
                }
            }
        }

        return $this->files;
    }

    public function findFiles()
    {
        $this->files = null;
        return $this->getFiles();
    }

    public function generateFileName($file = '')
    {
        if (Config::get('smokeping.integration') === true) {
            return Config::get('smokeping.dir') . '/' . $this->device->type . '/' . $file;
        } else {
            return Config::get('smokeping.dir') . '/' . $file;
        }
    }

    public function otherGraphs($direction)
    {
        $remote = $direction == 'in' ? 'src' : 'dest';
        $data = array_keys(array_filter($this->getFiles()[$direction][$this->device->hostname], function ($file) {
            return Str::contains($file, '~');
        }));

        return array_map(function ($other) use ($direction, $remote) {
            $device = \DeviceCache::getByHostname($other);
            return [
                'device' => $device,
                'graph' => [
                    'type' => 'smokeping_' . $direction,
                    'device' => $this->device->device_id,
                    $remote => $device->device_id,
                ]
            ];
        }, $data);
    }

    public function hasGraphs()
    {
        return $this->hasInGraph() || $this->hasOutGraph();
    }

    public function hasInGraph()
    {
        return !empty($this->getFiles()['in'][$this->device->hostname]);
    }

    public function hasOutGraph()
    {
        return !empty($this->getFiles()['out'][$this->device->hostname]);
    }

    private function filenameToHostname($name)
    {
        $name = str_replace('.rrd', '', $name);
        return str_replace('_', '.', $name);
    }
}
