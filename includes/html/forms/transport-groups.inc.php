<?php

/**
 * transport-groups.inc.php
 *
 * LibreNMS alert-transportsinc.php for processor
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
 * @copyright  2018 Vivia Nguyen-Tran
 * @author     Vivia Nguyen-Tran <vivia@ualberta.ca>
 */

use App\Models\AlertTransport;
use App\Models\AlertTransportGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

header('Content-type: application/json');

if (Gate::denies('update', AlertTransport::class)) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'You need to be admin',
    ]));
}

$status = 'ok';
$message = '';

$group_id = $vars['group_id'];
$name = strip_tags((string) $vars['name']);

$target_members = [];
foreach ((array) ($vars['members'] ?? []) as $target) {
    $target_members[] = (int) $target;
}

if (empty($name)) {
    $status = 'error';
    $message = 'No transport group name provided';
} elseif (count($target_members) < 1) {
    // Not enough members for a group; requires 1 at least
    $status = 'error';
    $message = 'Not enough group members';
} else {
    if (is_numeric($group_id) && $group_id > 0) {
        AlertTransportGroup::where('transport_group_id', $group_id)->update(['transport_group_name' => $name]);
    } else {
        // Insert into db
        $group_id = dbInsert([
            'transport_group_name' => $name,
        ], 'alert_transport_groups');
    }

    if (is_numeric($group_id) && $group_id > 0) {
        $db_members = AlertTransportGroup::find($group_id)
            ->transports()
            ->pluck('alert_transports.transport_id')
            ->all();

        // Compare arrays to get added and removed transports
        $add = array_diff($target_members, $db_members);
        $remove = array_diff($db_members, $target_members);

        // Insert new transport group members
        $insert = [];
        foreach ($add as $transport_id) {
            $insert[] = [
                'transport_id' => $transport_id,
                'transport_group_id' => $group_id,
            ];
        }
        if (! empty($insert)) {
            DB::table('transport_group_transport')->insert($insert);
        }

        // Remove old transport group members
        if (! empty($remove)) {
            \App\Models\TransportGroupTransport::where('transport_group_id', $group_id)->whereIn('transport_id', $remove)->delete();
        }
        $message = 'Updated alert transport group';
    } else {
        $status = 'error';
        $message = 'Did not update alert transport group';
    }
}

exit(json_encode([
    'status' => $status,
    'message' => $message,
]));
