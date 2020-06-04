<?php
/**
 * alert-transports.inc.php
 *
 * LibreNMS alert-transports.inc.php for processor
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
 * @copyright  2018 Vivia Nguyen-Tran
 * @author     Vivia Nguyen-Tran <vivia@ualberta.ca>
 */

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

header('Content-type: application/json');

if (!Auth::user()->hasGlobalAdmin()) {
    die(json_encode([
        'status' => 'error',
        'message' => 'You need to be admin'
    ]));
}

$status = 'ok';
$message = '';

$transport_id        = $vars['transport_id'];
$name                = $vars['name'];
$is_default          = (int)(isset($vars['is_default']) && $vars['is_default'] == 'on');
$transport_type      = $vars['transport-type'];
$timerange = mres($vars['timerange']);
$start_hr = mres($vars['start_hr']);
$end_hr = mres($vars['end_hr']);
$timerange_day = mres($vars['timerange_day']);

if (empty($name)) {
    $status = 'error';
    $message = 'No transport name provided';
} elseif (empty($transport_type)) {
    $status = 'error';
    $message = 'Missing transport information';
} else {
    $details = array(
        'transport_name' => $name,
        'is_default' => $is_default
    );

    if (!in_array($timerange, array(0,1))) {
        $message .= 'Missing timerange choice<br />';
    }

    // check values if timerange is set to yes
    if ($timerange == 1) {
        if (empty($start_hr)) {
            $message .= 'Missing start timerange hour<br />';
        }

        if (empty($end_hr)) {
            $message .= 'Missing end timerange hour<br />';
        }

        if (isset($vars['timerange_day']) && is_array($vars['timerange_day']) && !empty($vars['timerange_day'])) {
            $timerange_day = implode(',', $vars['timerange_day']);
        } else {
            $timerange_day = null;
        }

        if (!is_array($vars['maps'])) {
            $message .= 'Not mapped to any groups or devices<br />';
        }
    } else {
        // timerange = 0 => empty no reccurency values to be sure.
        $start_hr = '00:00:00';
        $end_hr = '00:00:00';
        $timerange_day = null;
    }

    if (empty($message)) {
        if (empty($transport_id)) {
            $transport_id = dbInsert(array('timerange' => $timerange, 'start_hr' => $start_hr, 'end_hr' => $end_hr, 'timerange_day' => $timerange_day, 'title' => $title, 'notes' => $notes), 'alert_schedule');
        } else {
            dbUpdate(array('timerange' => $timerange, 'start_hr' => $start_hr, 'end_hr' => $end_hr, 'timerange_day' => $timerange_day, 'title' => $title, 'notes' => $notes), 'alert_schedule', '`schedule_id`=?', array($transport_id));
        }

        if ($transport_id > 0) {
            $items = array();
            $fail  = 0;

            if ($update == 1) {
                dbDelete('alert_schedulables', '`schedule_id`=?', array($transport_id));
            }

            foreach ($_POST['maps'] as $target) {
                $type = 'device';
                if (Str::startsWith($target, 'l')) {
                    $type = 'location';
                    $target = substr($target, 1);
                } elseif (Str::startsWith($target, 'g')) {
                    $type = 'device_group';
                    $target = substr($target, 1);
                }

                $item = dbInsert(['schedule_id' => $transport_id, 'alert_schedulable_type' => $type, 'alert_schedulable_id' => $target], 'alert_schedulables');
                if ($notes && $type = 'device' && get_user_pref('add_schedule_note_to_device', false)) {
                    $device_notes = dbFetchCell('SELECT `notes` FROM `devices` WHERE `device_id` = ?;', [$target]);
                    $device_notes.= ((empty($device_notes)) ? '' : PHP_EOL) . date("Y-m-d H:i") . ' Alerts delayed: ' . $notes;
                    dbUpdate(['notes' => $device_notes], 'devices', '`device_id` = ?', [$target]);
                }
                if ($item > 0) {
                    array_push($items, $item);
                } else {
                    $fail = 1;
                }
            }

            if ($fail == 1 && $update == 0) {
                foreach ($items as $item) {
                    dbDelete('alert_schedulables', '`item_id`=?', array($item));
                }

                dbDelete('alert_schedule', '`schedule_id`=?', array($transport_id));
                $message = 'Issue scheduling maintenance';
            } else {
                $status  = 'ok';
                $message = 'Scheduling maintenance ok';
            }
        } else {
            $message = 'Issue scheduling maintenance';
        }//end if
    }//end if


    if (is_numeric($transport_id) && $transport_id > 0) {
        // Update the fields -- json config field will be updated later
        dbUpdate($details, 'alert_transports', 'transport_id=?', [$transport_id]);
    } else {
        // Insert the new alert transport
        $newEntry = true;
        $transport_id = dbInsert($details, 'alert_transports');
    }

    if ($transport_id) {
        $class = 'LibreNMS\\Alert\\Transport\\'.ucfirst($transport_type);

        if (!method_exists($class, 'configTemplate')) {
            die(json_encode([
                'status' => 'error',
                'message' => 'This transport type is not yet supported'
            ]));
        }
        
        // Build config values
        $result = call_user_func_array($class.'::configTemplate', []);
        $loader = new FileLoader(new Filesystem, "$install_dir/resources/lang");
        $translator = new Translator($loader, 'en');
        $validation = new Factory($translator, new Container);
        $validator = $validation->make($vars, $result['validation']);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                $message .= "$error<br>";
            }
            $status = 'error';
        } else {
            $transport_config = (array)json_decode(dbFetchCell('SELECT transport_config FROM alert_transports WHERE transport_id=?', [$transport_id]), true);
            foreach ($result['config'] as $tmp_config) {
                if (isset($tmp_config['name']) && $tmp_config['type'] !== 'hidden') {
                    $transport_config[$tmp_config['name']] = $vars[$tmp_config['name']];
                }
            }
            //Update the json config field
            $detail = [
                'transport_type' => $transport_type,
                'transport_config' => json_encode($transport_config)
            ];
            $where = 'transport_id=?';

            dbUpdate($detail, 'alert_transports', $where, [$transport_id]);

            $status = 'ok';
            $message = 'Updated alert transports';
        }
        if ($status == 'error' && $newEntry) {
            //If error, we will have to delete the new entry in alert_transports tbl
            $where = '`transport_id`=?';
            dbDelete('alert_transports', $where, [$transport_id]);
        }
    } else {
        $status = 'error';
        $message = 'Failed to update transport';
    }
}

die(json_encode([
    'status'       => $status,
    'message'      => $message
]));
