<?php

echo "
<h3><span class='label label-success threeqtr-width'>Add Service Template</span></h3>
<form id='addsrv-template' name='addsrv-template' method='post' action='' class='form-horizontal' role='form'>
  " . csrf_field() . "
  <div class='well well-lg'>
    <div class='form-group'>
      <input type='hidden' name='addsrv-template' value='yes'>
      <label for='device_group' class='col-sm-2 control-label'>Device Group</label>
      <div class='col-sm-5'>
        <select name='device_group' class='form-control input-sm'>
          $devicegroupsform
        </select>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='type' class='col-sm-2 control-label'>Type</label>
      <div class='col-sm-5'>
        <select name='type' id='type' class='form-control input-sm'>
          $servicesform
        </select>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='descr' class='col-sm-2 control-label'>Description</label>
      <div class='col-sm-5'>
        <textarea name='descr' id='descr' class='form-control input-sm' rows='5'></textarea>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='params' class='col-sm-2 control-label'>Parameters</label>
      <div class='col-sm-5'>
        <input name='params' id='params' class='form-control input-sm'>
      </div>
      <div class='col-sm-5'>
          This may be required based on the service check.
      </div>
    </div>
    <button type='submit' name='Submit' class='btn btn-success input-sm'>Add Service Template</button>
  </div>
</form>";
