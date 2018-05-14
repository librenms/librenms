<?php
/**
 * contact-groups.inc.php
 *
 * LibreNMS alert-contactsinc.php for processor
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

use LibreNMS\Authentication\Auth;

header('Content-type: application/json');

if (!Auth::user()->hasGlobalAdmin()) {
    die(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin'
    ]));
}

$status = 'ok';
$message = '';

$group_id            = mres($_POST['group_id']);
$name                = mres($_POST['name']);

$target_members = [];
foreach ((array)$_POST['members'] as $target) {
    $target_members[] = (int)$target;
}

if (empty($name)) {
    $status = 'error';
    $message = 'No contact group name provided';
} elseif (sizeof($target_members) <= 1) {
    // Not enough members for a group; requires 2 at least
    $status = 'error';
    $message = 'Not enough group members';
} else {
    if (is_numeric($group_id) && $group_id > 0) {
        dbUpdate(array(
            'contact_group_name' => $name
        ), 'alert_contact_groups', "`contact_group_id`=?", [$group_id]);
    } else {
        // Insert into db
        $group_id = dbInsert(array(
            'contact_group_name' => $name
        ), 'alert_contact_groups');
    }
    
    if (is_numeric($group_id) && $group_id > 0) {
        $sql = "SELECT `contact_id` FROM `contact_group_contact` WHERE `contact_group_id`=?";
        $db_members = dbFetchColumn($sql, [$group_id]);

        // Compare arrays to get added and removed contacts
        $add = array_diff($target_members, $db_members);
        $remove = array_diff($db_members, $target_members);

        // Insert new contact group members
        $insert = [];
        foreach ($add as $contact_id) {
            $insert[] = array(
                'contact_id' => $contact_id,
                'contact_group_id' => $group_id
            );
        }
        if (!empty($insert)) {
            dbBulkInsert($insert, 'contact_group_contact');
        }

        // Remove old contact group members
        if (!empty($remove)) {
            dbDelete('contact_group_contact', 'contact_group_id=? AND `contact_id` IN (?)', array($group_id, array(implode(',', $remove))));
        }
        $message = 'Updated alert contact group';
    } else {
        $status = 'error';
        $message = 'ERROR: Did not update alert contact group';
    }
}

die(json_encode([
    'status'       => $status,
    'message'      => $message
]));
