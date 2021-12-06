<?php
/**
 * Color.php
 *
 * Misc color generation
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Port;

class Color
{
    /**
     * Get colors for a percentage bar based on current percentage
     *
     * @param  int|float  $percentage
     * @param  int|float  $component_perc_warn
     * @return string[]
     */
    public static function percentage($percentage, $component_perc_warn = null): array
    {
        $perc_warn = 75;

        if (isset($component_perc_warn)) {
            $perc_warn = round($component_perc_warn, 0);
        }

        if ($percentage > $perc_warn) {
            return [
                'left' => 'c4323f',
                'right' => 'c96a73',
                'middle' => 'c75862',
            ];
        }

        if ($percentage > 75) {
            return [
                'left' => 'bf5d5b',
                'right' => 'd39392',
                'middle' => 'c97e7d',
            ];
        }

        if ($percentage > 50) {
            return [
                'left' => 'bf875b',
                'right' => 'd3ae92',
                'middle' => 'cca07e',
            ];
        }

        if ($percentage > 25) {
            return [
                'left' => '5b93bf',
                'right' => '92b7d3',
                'middle' => '7da8c9',
            ];
        }

        return [
            'left' => '9abf5b',
            'right' => 'bbd392',
            'middle' => 'afcc7c',
        ];
    }

    /**
     * Get highlight color based on device status
     */
    public static function forDeviceStatus(Device $device): string
    {
        if ($device->disabled) {
            return '#808080';
        }

        if ($device->ignore) {
            return '#000000';
        }

        return $device->status ? '#008000' : '#ff0000';
    }

    /**
     * Get highlight color based on Port status
     */
    public static function forPortStatus(Port $port): string
    {
        // Ignored ports
        if ($port->ignore) {
            return '#000000';
        }

        // Shutdown ports
        if ($port->ifAdminStatus === 'down') {
            return '#808080';
        }

        // Down Ports
        if ($port->ifOperStatus !== 'up') {
            return '#ff0000';
        }

        // Errored ports
        if ($port->ifInErrors_delta > 0 || $port->ifOutErrors_delta > 0) {
            return '#ffa500';
        }

        // Up ports
        return '#008000';
    }

    /**
     * Get highlight color based on BgpPeer status
     */
    public static function forBgpPeerStatus(BgpPeer $peer): string
    {
        // Session inactive
        if ($peer->bgpPeerAdminStatus !== 'start') {
            return '#000000';
        }

        // Session active but errored
        if ($peer->bgpPeerState !== 'established') {
            return '#ffa500';
        }

        // Session Up
        return '#008000';
    }
}
