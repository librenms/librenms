<?php

/**
 * PhpRrdGraph.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\RRD\Graph;

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\RrdGraphException;
use Log;

class PhpRrdGraph implements RrdGraphInterface
{
    /**
     * @param  string[]  $options
     */
    public function graph(array $options): string
    {
        $savedEnv = $this->saveEnv();

        Log::info('PHPRRD[%ggraph ' . implode(' ', $options) . '%n]', ['color' => true]);
        $rrd = new \RRDGraph('-');
        $rrd->setOptions($options);
        try {
            $data = $rrd->saveVerbose();
        } catch (\Exception $e) {
            throw new RrdGraphException($e->getMessage());
        } finally {
            $this->restoreEnv($savedEnv);
        }

        return $data['image'];
    }

    /**
     * @param  string[]  $savedEnv
     */
    private function restoreEnv($savedEnv): void
    {
        foreach ($savedEnv as $key => $value) {
            if ($value) {
                putenv("$key=$value"); // Restore environment variable
            } else {
                putenv($key); // Remove environment variable
            }
        }
    }

    /**
     * @return string[]
     */
    private function saveEnv(): array
    {
        $ret = [
            'LC_ALL' => getenv('LC_ALL'),
            'TZ' => ('TZ'),
            'RRDCACHED_ADDRESS' => ('RRDCACHED_ADDRESS'),
        ];
        $rrdcached = LibrenmsConfig::get('rrdcached', '');

        putenv('LC_ALL=C'); // force english/standard output
        if ($rrdcached) {
            putenv('RRDCACHED_ADDRESS=' . $rrdcached);
        }
        if (session('preferences.timezone')) {
            putenv('TZ=' . session('preferences.timezone'));
        }

        return $ret;
    }
}
