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

if ($_SESSION['userlevel'] >= '10') {
if (empty($_POST['token'])) {
    $_POST['token'] = bin2hex(openssl_random_pseudo_bytes(16));
}

?>
  <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="Delete" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title" id="Delete">Confirm Delete</h5>
        </div>
        <div class="modal-body">
          <p>If you would like to remove the API token then please click Delete.</p>
        </div>
        <div class="modal-footer">
          <form role="form" class="remove_token_form">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger danger" id="token-removal" data-target="token-removal">Delete</button>
            <input type="hidden" name="token_id" id="token_id" value="">
            <input type="hidden" name="type" id="type" value="token-item-remove">
            <input type="hidden" name="confirm" id="confirm" value="yes">
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade bs-example-modal-sm" id="create-token" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title" id="Create">Create new API Access token</h5>
        </div>
        <div class="modal-body">
          <form role="form" class="form-horizontal create_token_form">
            <div class="form-group">
              <label for="user_id" class="col-sm-2 control-label">User: </label>
              <div class="col-sm-4">
                <select class="form-control" id="user_id" name="user_id">
<?php
foreach (dbFetchRows("SELECT user_id,username FROM `users` WHERE `level` >= '10'", array()) as $users) {
    echo '<option value="'.$users['user_id'].'">'.$users['username'].'</option>';
}

?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="token" class="col-sm-2 control-label">Token: </label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="token" name="token" value="<?php echo $_POST['token']; ?>" readonly>
              </div>
              <div class="col-sm-2">
              </div>
            </div>
            <div class="form-group">
              <label for="description" class="col-sm-2 control-label">Descr: </label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="description" name="description" value="<?php echo $_POST['description']; ?>">
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <div class="form-group">
            <div class="pull-right">
              <input type="hidden" name="type" id="type" value="token-item-create">
              <button type="submit" class="btn btn-success" name="token-create" id="token-create">Create API Token</button>
            </div>
          </div>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php
echo '
  <div class="row">
    <div class="col-md-12">
      <span id="thanks"></span>
    </div>
  </div>
';
if ($_SESSION['api_token'] === true) {
    echo "<script>
      $('#thanks').html('<div class=\"alert alert-info\">The API token has been added.</div>');</script>
    ";
    unset($_SESSION['api_token']);
}

echo '
  <h3>API Access</h3>
  <hr>
  <div class="row">
    <div class="col-sm-12">
      &nbsp;
    </div>
  </div>
      <table class="table table-bordered table-condensed">
        <tr>
          <th>User</th>
          <th>Token Hash</th>
          <th>Description</th>
          <th>Disabled</th>
          <th>Remove</th>
        </tr>
';

foreach (dbFetchRows('SELECT `AT`.*,`U`.`username` FROM `api_tokens` AS AT JOIN users AS U ON AT.user_id=U.user_id ORDER BY AT.user_id') as $api) {
    if ($api['disabled'] == '1') {
        $api_disabled = 'checked';
    }
    else {
        $api_disabled = '';
    }
    echo '
        <tr id="'.$api['id'].'">
          <td>'.$api['username'].'</td>
          <td>'.$api['token_hash'].'</td>
          <td>'.$api['description'].'</td>
          <td><input type="checkbox" name="token-status" data-token_id="'.$api['id'].'" data-off-text="No" data-on-text="Yes" data-on-color="danger" '.$api_disabled.' data-size="mini"></td>
          <td><button type="button" class="btn btn-danger btn-xs" id="'.$api['id'].'" data-token_id="'.$api['id'].'" data-toggle="modal" data-target="#confirm-delete">Delete</button></td>
        </tr>
';
}

  echo '
      </table>
      <center>
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-token">Create API access token</button>
      </center>
';
?>
<script>
  $("[name='token-status']").bootstrapSwitch('offColor','success');
  $('input[name="token-status"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var $this = $(this);
    var token_id = $(this).data("token_id");
    $.ajax({
      type: 'POST',
      url: 'ajax_form.php',
      data: { type: "token-item-disable", token_id: token_id, state: state},
      dataType: "html",
      success: function(data){
        //alert('good');
      },
      error:function(){
        //alert('bad');
      }
    });
  });
  $('#confirm-delete').on('show.bs.modal', function(event) {
    token_id = $(event.relatedTarget).data('token_id');
    $("#token_id").val(token_id);
  });
  $('#token-removal').click('', function(event) {
    event.preventDefault();
    token_id = $("#token_id").val();
    $.ajax({
      type: "POST",
      url: "ajax_form.php",
      data: $('form.remove_token_form').serialize() ,
      success: function(msg){
        $("#thanks").html('<div class="alert alert-info">'+msg+'</div>');
        $("#confirm-delete").modal('hide');
        $("#"+token_id).remove();
      },
      error: function(){
        $("#thanks").html('<div class="alert alert-info">An error occurred removing the token.</div>');
        $("#confirm-delete").modal('hide');
      }
    });
  });
  $('#token-create').click('', function(event) {
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "ajax_form.php",
      data: $('form.create_token_form').serialize(),
      success: function(msg){
        $("#thanks").html('<div class="alert alert-info">'+msg+'</div>');
        $("#create-token").modal('hide');
        if(msg.indexOf("ERROR:") <= -1) {
          location.reload();
        }
      },
      error: function(){
        $("#thanks").html('<div class="alert alert-info">An error occurred removing the token.</div>');
        $("#create-token").modal('hide');
      }
    });
  });
  $('#pass-gen').click('', function(event) {
    event.preventDefault();
    token = $.password(32,false);
    $('#token').val(token);
  });
</script>

<?php
}
else {
    include 'includes/error-no-perm.inc.php';
}//end if
