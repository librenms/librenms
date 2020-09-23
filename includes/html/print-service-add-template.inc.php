<?php

echo "
<form id='addsrv-template' name='addsrv-template' method='post' action='' class='form-horizontal' role='form'>
  " . csrf_field() . "
  <div><h2>Add Service Template</h2></div>
  <div class='alert alert-info'>Service Template will created for the specified Device Group.</div>
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
      <label for='ip' class='col-sm-2 control-label'>IP Address</label>
      <div class='col-sm-5'>
        <input name='ip' id='ip' class='form-control input-sm' placeholder='IP Address'>
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
    <div class='form-group'>
      <label for='ignore' class='col-sm-2 control-label'>Ignore alert tag </label>
      <div class='col-sm-5'>
        <input name='ignore' id='ignore' type='checkbox'>
      </div>
    </div>
    <div class='form-group'>
      <label for='disabled' class='col-sm-2 control-label'>Disable polling and alerting </label>
      <div class='col-sm-5'>
        <input name='disabled' id='disabled' type='checkbox'>
      </div>
    </div>
    <button type='submit' name='Submit' class='btn btn-success input-sm'>Add Service Template</button>
  </div>
</form>";
