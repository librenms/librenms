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

?>

<form id='addsrv' name='addsrv' method='post' action='' class='form-horizontal' role='form'>
    <div class='form-group row'>
        <input type='hidden' name='addsrv' value='yes'>
        <label for='device' class='col-sm-2 control-label'>Device</label>
        <div class='col-sm-5'>
            <select name='device' class='form-control input-sm'>
                <?php echo $devicesform; ?>
            </select>
        </div>
    </div>
    <div class='form-group row'>
        <label for='type' class='col-sm-2 control-label'>Type</label>
        <div class='col-sm-5'>
            <select name='type' id='type' class='form-control input-sm'>
                <?php echo $servicesform; ?>
            </select>
        </div>
    </div>
    <div class='form-group row'>
        <label for='descr' class='col-sm-2 control-label'>Description</label>
        <div class='col-sm-5'>
            <textarea name='descr' id='descr' class='form-control input-sm' rows='5'></textarea>
        </div>
    </div>
    <div class='form-group row'>
        <label for='ip' class='col-sm-2 control-label'>IP Address</label>
        <div class='col-sm-5'>
            <input name='ip' id='ip' class='form-control input-sm' placeholder='IP Address'>
        </div>
    </div>
    <div class='form-group row'>
        <label for='params' class='col-sm-2 control-label'>Parameters</label>
        <div class='col-sm-5'>
            <input name='params' id='params' class='form-control input-sm'>
        </div>
        <div class='col-sm-5'>
            This may be required based on the service check.
        </div>
    </div>
    <div class="row">
        <div class="col-sm-offset-2">
            <button type='submit' name='Submit' class='btn btn-success btn-sm col-sm-offset-2'>Add Service</button>
        </div>
    </div>
    <br>
</form>
