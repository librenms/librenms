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

use LibreNMS\Authentication\LegacyAuth;

$status = 'error';

if (!LegacyAuth::user()->hasGlobalAdmin()) {
    header('Content-Type: application/json');
    $response = array('status' => $status, 'message' => 'You need to be admin');
    die(json_encode($response));
}

$template_id = 0;

$name = mres($vars['name']);
if (isset($vars['template']) && empty(view(['template' => $vars['template']], [])->__toString())) {
    $message = 'Template failed to be parsed, please check the syntax';
} elseif (!empty($name)) {
    if ((isset($vars['template_id']) && is_numeric($vars['template_id'])) && (isset($vars['rule_id']) && $vars['rule_id'])) {
        //Update the template/rule mapping

        if (is_array($vars['rule_id'])) {
            $vars['rule_id'] = implode(",", $vars['rule_id']);
        }
        if (substr($vars['rule_id'], 0, 1) != ",") {
            $vars['rule_id'] = ",".$vars['rule_id'];
        }
        if (substr($vars['rule_id'], -1, 1) != ",") {
            $vars['rule_id'] .= ",";
        }
        if (dbUpdate(array('rule_id' => mres($vars['rule_id']), 'name' => $name), "alert_templates", "id = ?", array($vars['template_id'])) >= 0) {
            $message = "Updated template and rule id mapping";
        } else {
            $message ="Failed to update the template and rule id mapping";
        }
    } elseif ($vars['template'] && is_numeric($vars['template_id'])) {
        //Update template-text
        if (dbUpdate(array('template' => $vars['template'], 'name' => $name, 'title' => $vars['title'], 'title_rec' => $vars['title_rec']), "alert_templates", "id = ?", array($vars['template_id'])) >= 0) {
            $status = 'ok';
            $message = "Alert template updated";
        } else {
            $message = "Failed to update the template";
        }
    } elseif ($vars['template']) {
        //Create new template

        if ($name != 'Default Alert Template') {
            $template_id = dbInsert(array('template' => $vars['template'], 'name' => $name, 'title' => $vars['title'], 'title_rec' => $vars['title_rec']), "alert_templates");
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

echo _json_encode($response);
