<?php
/*
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
$page_title = 'Alerts';
?>

<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>Alerts</strong>
    </div>

    <?php
    $device['device_id'] = '-1';
    require_once 'includes/html/modal/alert_details.php';
    require_once 'includes/html/modal/alert_notes.inc.php';
    require_once 'includes/html/modal/alert_ack.inc.php';
    require_once 'includes/html/common/alerts.inc.php';
    echo implode('', $common_output);
    unset($device['device_id']);
    ?>
</div>


