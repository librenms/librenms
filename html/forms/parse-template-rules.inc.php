<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if(is_admin() === false) {
    die('ERROR: You need to be admin');
}

$template_id = ($_POST['template_id']);

if(is_numeric($template_id) && $template_id > 0) {
    $template = dbFetchCell("SELECT `rule_id` FROM `alert_templates` WHERE `id` = ? LIMIT 1",array($template_id));
    $rule_id = split(",", $template);
    $output = array('rule_id'=>$rule_id);
    echo _json_encode($output);
}
