<?php
/**
 * delete-cluster-poller.inc.php
 *
 * Handle poller delete request
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

if (!Auth::user()->hasGlobalAdmin()) {
    $status = array('status' =>1, 'message' => 'ERROR: You need to be admin to delete poller entries');
} else {
    $id = $vars['id'];
    if (!is_numeric($id)) {
        $status = array('status' =>1, 'message' => 'No poller has been selected');
    } else {
        $poller_name = dbFetchCell('SELECT `poller_name` FROM `pollers` WHERE `id`=?', array($id));
        if (dbDelete('poller_cluster', 'id=?', array($id)) && dbDelete('poller_cluster_stats', 'parent_poller=?', array($id))) {
            $status = array('status' => 0, 'message' => "Poller: <i>$poller_name ($id), has been deleted.</i>");
        } else {
            $status = array('status' => 1, 'message' => "Poller: <i>$poller_name ($id), has NOT been deleted.</i>");
        }
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
