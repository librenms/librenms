<?php
/**
 * application-update.php
 *
 * Handle application enable disable from ajax
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
    $status = array('status' => 1, 'message' => 'You need to be admin');
} else {
    $device_id = $_POST['device_id'];
    $app = $_POST['application'];

    if (!isset($app) && validate_device_id($device_id) === false) {
        $status = array('status' => 1, 'message' => 'Error with data');
    } else {
        $status = array('status' => 1, 'message' => 'Database update failed');
        if ($_POST['state'] == 'true') {
            $update = array(
                'device_id' => $device_id,
                'app_type' => $app,
                'app_status' => '',
                'app_instance' => ''
            );
            if (dbInsert($update, 'applications')) {
                log_event("Application enabled by user: $app", $device_id, 'application', 1);
                $status = array('status' => 0, 'message' => 'Application enabled');
            }
        } else {
            if (dbDelete('applications', '`device_id`=? AND `app_type`=?', array($device_id, $app))) {
                log_event("Application disabled by user: $app", $device_id, 'application', 3);
                $status = array('status' => 0, 'message' => 'Application disabled');
            }
        }
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
