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

print_optionbar_start();
echo "<span style='font-weight: bold;'>Miscellaneous settings</span>";
print_optionbar_end();
?>

<form class="form-horizontal">
    <div class="form-group">
        <label for="icmp" class="col-sm-4 control-label">Disable ICMP Test?</label>
        <div class="col-sm-8">
            <?php echo dynamic_override_config('checkbox', 'override_icmp_disable', $device); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="oxidized" class="col-sm-4 control-label">Exclude from Oxidized?</label>
        <div class="col-sm-8">
            <?php echo dynamic_override_config('checkbox', 'override_Oxidized_disable', $device); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="unixagent" class="col-sm-4 control-label">Unix agent port</label>
        <div class="col-sm-8">
            <?php echo dynamic_override_config('text', 'override_Unixagent_port', $device); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="unixagent" class="col-sm-4 control-label">Enable RRD Tune for all ports?</label>
        <div class="col-sm-8">
            <?php echo dynamic_override_config('checkbox', 'override_rrdtool_tune', $device); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="selected_ports" class="col-sm-4 control-label">Enable selected port polling?</label>
        <div class="col-sm-8">
            <?php echo dynamic_override_config('checkbox', 'selected_ports', $device); ?>
        </div>
    </div>
</form>
