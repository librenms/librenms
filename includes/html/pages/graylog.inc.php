<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/
$no_refresh = true;
$pagetitle[] = 'Graylog';

echo '<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>Graylog entries</strong>
    </div>';

require_once 'includes/html/common/graylog.inc.php';
echo implode('', $common_output);

echo '</div>';
