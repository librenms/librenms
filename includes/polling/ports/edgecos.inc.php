<?php
/**
* edgecos.inc.php
*
* LibreNMS Edgecos Ports fix
*
* Fix the problem that EdgeCore did not follow the ifOperStatus convention
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
* @copyright  2019 Ming-Han Yang
* @author     Ming-Han Yang <soto2080edu@g.ncu.edu.tw>
*/
foreach ($port_stats as & $port) {
    if ($port['ifOperStatus'] == 'lowerLayerDown') {
        $port['ifOperStatus'] = 'down';
    }
}
unset($port);
d_echo($port_stats);
