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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

if (is_admin() === false) {
    $status = array('status' =>1, 'message' => 'ERROR: You need to be admin to delete poller entries');
} else {
    if (!is_numeric($vars['id'])) {
        $status = array('status' =>1, 'message' => 'No poller has been selected');
    } else {
        if (dbDelete('pollers', 'id=?', array($vars['id']))) {
            $status = array('status' =>0, 'message' => 'Poller: <i>'.$vars['id'].', has been deleted.</i>');
        } else {
            $status = array('status' =>1, 'message' => 'Poller: <i>'.$vars['id'].', has NOT been deleted.</i>');
        }
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
