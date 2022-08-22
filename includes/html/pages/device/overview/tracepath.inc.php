<?php
/**
 * tracepath.inc.php
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

use App\Models\DevicePerf;

$perf_info = DevicePerf::where('device_id', $device['device_id'])->latest('timestamp')->first();
if ($perf_info['debug']['traceroute']) {
    echo "
<div class='row'>
     <div class='col-md-12'>
         <div class='panel panel-default'>
             <div class='panel-heading'>
                 <h3 class='panel-title'>Traceroute ({$perf_info['timestamp']})</h3>
             </div>
             <div class='panel-body'>
                 <pre>{$perf_info['debug']['traceroute']}</pre>
            </div>
         </div>
     </div>
 </div>";
}
