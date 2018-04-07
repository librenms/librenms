<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Alert Templates
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

use LibreNMS\Authentication\Auth;

header('Content-type: text/plain');

if (!Auth::user()->hasGlobalAdmin()) {
    die('ERROR: You need to be admin');
}

$status = 'error';
$template_id = 0;

$name = mres($_POST['name']);
if (!empty($name)) {
    if (is_numeric($_REQUEST['template_id']) && $_REQUEST['rule_id']) {
        //Update the template/rule mapping

        if (is_array($_REQUEST['rule_id'])) {
            $_REQUEST['rule_id'] = implode(",", $_REQUEST['rule_id']);
        }
        if (substr($_REQUEST['rule_id'], 0, 1) != ",") {
            $_REQUEST['rule_id'] = ",".$_REQUEST['rule_id'];
        }
        if (substr($_REQUEST['rule_id'], -1, 1) != ",") {
            $_REQUEST['rule_id'] .= ",";
        }
        if (dbUpdate(array('rule_id' => mres($_REQUEST['rule_id']), 'name' => $name), "alert_templates", "id = ?", array($_REQUEST['template_id'])) >= 0) {
            $message = "Updated template and rule id mapping";
        } else {
            $message ="Failed to update the template and rule id mapping";
        }
    } elseif ($_REQUEST['template'] && is_numeric($_REQUEST['template_id'])) {
        //Update template-text

        if (dbUpdate(array('template' => $_REQUEST['template'], 'name' => $name, 'title' => $_REQUEST['title'], 'title_rec' => $_REQUEST['title_rec']), "alert_templates", "id = ?", array($_REQUEST['template_id'])) >= 0) {
            $status = 'ok';
            $message = "Alert template updated";
        } else {
            $message = "Failed to update the template";
        }
    } elseif ($_REQUEST['template']) {
        //Create new template

        if ($name != 'Default Alert Template') {
            $template_id = dbInsert(array('template' => $_REQUEST['template'], 'name' => $name, 'title' => $_REQUEST['title'], 'title_rec' => $_REQUEST['title_rec']), "alert_templates");
            if ($template_id != false) {
                $status = 'ok';
                $message = "Alert template has been created.";
            } else {
                $message = "Could not create alert template";
            }
        } else {
            $message = "This template name is reserved!";
        }
    } else {
        $message = "We could not work out what you wanted to do!";
    }
} else {
    $message = "You haven't given your template a name, it feels sad :( - $name";
}

$response = array('status' => $status, 'message' => $message, 'newid' => $template_id);

header('Content-Type: application/json');
echo _json_encode($response);
