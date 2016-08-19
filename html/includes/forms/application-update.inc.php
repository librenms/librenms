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

header('Content-type: text/plain');

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

$device_id = $_POST['device_id'];
$app = $_POST['application'];

if (!isset($app) && validate_device_id($device_id) === false) {
    echo 'error with data';
    exit;
} else {
    if ($_POST['state'] == 'true') {
        $update = array(
            'device_id' => $device_id,
            'app_type' => $app,
            'app_status' => '',
            'app_instance' => ''
        );
        if (dbInsert($update, 'applications')) {
            log_event("Application enabled by user: $app", $device_id, 'application', 1);
        }
    } else {
        if (dbDelete('applications', '`device_id`=? AND `app_type`=?', array($device_id, $app))) {
            log_event("Application disabled by user: $app", $device_id, 'application', 3);
        }
    }
}
