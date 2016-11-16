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
header('Content-type: text/plain');

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

$ok = '';
$error = '';
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
            $ok = "Updated template and rule id mapping";
        } else {
            $error ="Failed to update the template and rule id mapping";
        }
    } elseif ($_REQUEST['template'] && is_numeric($_REQUEST['template_id'])) {
        //Update template-text

        if (dbUpdate(array('template' => $_REQUEST['template'], 'name' => $name, 'title' => $_REQUEST['title'], 'title_rec' => $_REQUEST['title_rec']), "alert_templates", "id = ?", array($_REQUEST['template_id'])) >= 0) {
            $ok = "Updated template";
        } else {
            $error = "Failed to update the template";
        }
    } elseif ($_REQUEST['template']) {
        //Create new template

        if (dbInsert(array('template' => $_REQUEST['template'], 'name' => $name, 'title' => $_REQUEST['title'], 'title_rec' => $_REQUEST['title_rec']), "alert_templates")) {
            $ok = "Alert template has been created.";
        } else {
            $error = "Could not create alert template";
        }
    } else {
        $error = "We could not work out what you wanted to do!";
    }
} else {
    $error = "You haven't given your template a name, it feels sad :( - $name";
}

if (!empty($ok)) {
    die("$ok");
} else {
    die("ERROR: $error");
}
