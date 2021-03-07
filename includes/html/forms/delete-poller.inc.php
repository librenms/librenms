<?php
/**
 * delete-poller.inc.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
if (! Auth::user()->hasGlobalAdmin()) {
    $status = ['status' =>1, 'message' => 'ERROR: You need to be admin to delete poller entries'];
} else {
    $id = $vars['id'];
    if (! is_numeric($id)) {
        $status = ['status' =>1, 'message' => 'No poller has been selected'];
    } else {
        $poller_name = dbFetchCell('SELECT `poller_name` FROM `pollers` WHERE `id`=?', [$id]);
        if (dbDelete('pollers', 'id=?', [$id])) {
            $status = ['status' => 0, 'message' => "Poller: <i>$poller_name ($id), has been deleted.</i>"];
        } else {
            $status = ['status' => 1, 'message' => "Poller: <i>$poller_name ($id), has NOT been deleted.</i>"];
        }
    }
}
header('Content-Type: application/json');
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
