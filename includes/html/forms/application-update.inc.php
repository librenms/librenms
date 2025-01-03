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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Enum\Severity;

if (! Auth::user()->hasGlobalAdmin()) {
    $status = ['status' => 1, 'message' => 'You need to be admin'];
} else {
    $device = Device::find($_POST['device_id']);
    $app = $_POST['application'];

    if (! isset($app) && $device === null) {
        $status = ['status' => 1, 'message' => 'Error with data'];
    } else {
        $status = ['status' => 1, 'message' => 'Database update failed'];
        $app = $device->applications()->withTrashed()->firstOrNew(['app_type' => $app]);
        if ($_POST['state'] == 'true') {
            if ($app->trashed()) {
                $app->restore();
            }
            if ($app->save()) {
                Eventlog::log('Application enabled by user ' . Auth::user()->username . ': ' . $app, $device->device_id, 'application', Severity::Ok);
                $status = ['status' => 0, 'message' => 'Application enabled'];
            } else {
                $status = ['status' => 1, 'message' => 'Database update for enabling the application failed'];
            }
        } else {
            $app->delete();
            if ($app->save()) {
                Eventlog::log('Application disabled by user ' . Auth::user()->username . ': ' . $app, $device->device_id, 'application', Severity::Notice);
                $status = ['status' => 0, 'message' => 'Application disabled'];
            } else {
                $status = ['status' => 1, 'message' => 'Database update for disabling the application failed'];
            }
        }
    }
}
header('Content-Type: application/json');
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
