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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 * Alert Templates
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */
$status = 'error';

if (! Auth::user()->hasGlobalAdmin()) {
    header('Content-Type: application/json');
    $response = ['status' => $status, 'message' => 'You need to be admin'];
    exit(json_encode($response));
}

$template_id = 0;
$template_newid = 0;
$create = true;

$name = $vars['name'];
if (isset($vars['template']) && empty(view(['template' => $vars['template']], [])->__toString())) {
    $message = 'Template failed to be parsed, please check the syntax';
} elseif (! empty($name)) {
    if ($vars['template'] && is_numeric($vars['template_id'])) {
        // Update template
        $create = false;
        $template_id = $vars['template_id'];
        if (! dbUpdate(['template' => $vars['template'], 'name' => $name, 'title' => $vars['title'], 'title_rec' => $vars['title_rec']], 'alert_templates', 'id = ?', [$template_id]) >= 0) {
            $status = 'ok';
        } else {
            $message = 'Failed to update the template';
        }
    } elseif ($vars['template']) {
        // Create template
        if ($name != 'Default Alert Template') {
            $template_newid = dbInsert(['template' => $vars['template'], 'name' => $name, 'title' => $vars['title'], 'title_rec' => $vars['title_rec']], 'alert_templates');
            if ($template_newid != false) {
                $template_id = $template_newid;
                $status = 'ok';
            } else {
                $message = 'Could not create alert template';
            }
        } else {
            $message = 'This template name is reserved!';
        }
    } else {
        $message = 'We could not work out what you wanted to do!';
    }
    if ($status == 'ok') {
        $alertRulesOk = true;
        dbDelete('alert_template_map', 'alert_templates_id = ?', [$template_id]);
        $rules = explode(',', $vars['rules']);
        if ($rules !== false) {
            foreach ($rules as $rule_id) {
                if (! dbInsert(['alert_rule_id' => $rule_id, 'alert_templates_id' => $template_id], 'alert_template_map')) {
                    $alertRulesOk = false;
                }
            }
        }
        if ($alertRulesOk) {
            $status = 'ok';
            $message = 'Alert template has been ' . ($create ? 'created' : 'updated') . ' and attached rules have been updated.';
        } else {
            $status = 'warning';
            $message = 'Alert template has been ' . ($create ? 'created' : 'updated') . ' but some attached rules have not been updated.';
        }
    }
} else {
    $message = "You haven't given name to your template";
}

$response = ['status' => $status, 'message' => $message, 'newid' => $template_newid];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
