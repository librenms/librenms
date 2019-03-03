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
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     LibreNMS Contributors
*/
$pagetitle[] = 'Virtual Machines';
?>
<table class="table">
    <thead>
    <th>Server Name</th>
    <th>Power</th>
    <th>Operating System</th>
    <th>Memory</th>
    <th>CPU</th>
    </thead>
    <tbody>
    <?php
    foreach (dbFetchRows('SELECT `vmwVmDisplayName`,`vmwVmState`,`vmwVmGuestOS`,`vmwVmMemSize`,`vmwVmCpus` FROM `vminfo` WHERE `device_id` = ? ORDER BY `vmwVmDisplayName`', array($device['device_id'])) as $vm) {
        include 'includes/print-vm.inc.php';
    }
    ?>
    </tbody>
</table>
