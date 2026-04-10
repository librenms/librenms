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

use App\Models\ApiToken;
use App\Models\User;
use LibreNMS\Authentication\LegacyAuth;

if (Gate::any(['api.access', 'api.management'])) {
    $canManage = Gate::allows('api.management');
    $userlist = $canManage ? User::all() : collect();
?>

  <style>
    #confirm-delete, #create-token, #display-qr,
    #confirm-delete-v1, #create-v1-token, #renew-v1-token {
      z-index: 1200;
    }
    .modal-backdrop {
      z-index: 1150;
    }
  </style>

  <?php if ($canManage) { ?>
  <!-- V0 Modals -->
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
            <?php echo csrf_field() ?>
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
            <?php echo csrf_field() ?>
            <div class="form-group">
              <label for="user_id" class="col-sm-2 control-label">User: </label>
              <div class="col-sm-4">
                <select class="form-control" id="user_id" name="user_id">
    <?php
    foreach ($userlist as $user) {
        echo '<option value="' . $user->user_id . '">' . htmlentities((string) $user->username) . ' (' . htmlentities((string) $user->auth_type) . ')</option>';
    } ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="description" class="col-sm-2 control-label">Descr: </label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($_POST['description'] ?? ''); ?>">
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
  <div class="modal fade" id="display-qr" tabindex="-1" role="dialog" aria-labelledby="Delete" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title" id="Create">Scan the QR code below</h5>
        </div>
        <div class="modal-body">
          <div id="qrcode"></div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>

  <!-- V1 Modals -->
  <div class="modal fade" id="confirm-delete-v1" tabindex="-1" role="dialog" aria-labelledby="DeleteV1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title">Confirm Delete</h5>
        </div>
        <div class="modal-body">
          <p>If you would like to remove this V1 API token then please click Delete.</p>
        </div>
        <div class="modal-footer">
          <form role="form" class="remove_v1_token_form">
            <?php echo csrf_field() ?>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger" id="v1-token-removal">Delete</button>
            <input type="hidden" name="v1_token_id" id="v1_token_id" value="">
            <input type="hidden" name="type" value="v1-token-item-remove">
            <input type="hidden" name="confirm" value="yes">
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="renew-v1-token" tabindex="-1" role="dialog" aria-labelledby="RenewV1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title">Renew V1 API Token</h5>
        </div>
        <div class="modal-body">
          <form role="form" class="form-horizontal renew_v1_token_form">
            <?php echo csrf_field() ?>
            <div class="form-group">
              <label for="extend_days" class="col-sm-4 control-label">Expires in: </label>
              <div class="col-sm-8">
                <div class="input-group">
                  <input type="number" class="form-control" id="extend_days" name="extend_days" placeholder="30" min="1" required>
                  <span class="input-group-addon">days</span>
                </div>
              </div>
            </div>
            <input type="hidden" name="v1_token_id" id="renew_v1_token_id" value="">
            <input type="hidden" name="type" value="v1-token-item-renew">
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" id="v1-token-renew-submit">Renew</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="create-v1-token" tabindex="-1" role="dialog" aria-labelledby="CreateV1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title">Create new V1 API Token</h5>
        </div>
        <div class="modal-body">
          <form role="form" class="form-horizontal create_v1_token_form">
            <?php echo csrf_field() ?>
            <?php if ($canManage) { ?>
            <div class="form-group">
              <label for="v1_user_id" class="col-sm-2 control-label">User: </label>
              <div class="col-sm-4">
                <select class="form-control" id="v1_user_id" name="user_id">
    <?php
    foreach ($userlist as $user) {
        echo '<option value="' . $user->user_id . '">' . htmlentities((string) $user->username) . '</option>';
    } ?>
                </select>
              </div>
            </div>
            <?php } ?>
            <div class="form-group">
              <label for="token_name" class="col-sm-2 control-label">Name: </label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="token_name" name="token_name" placeholder="Token name" required>
              </div>
            </div>
            <div class="form-group">
              <label for="expires_in" class="col-sm-2 control-label">Expires: </label>
              <div class="col-sm-4">
                <input type="number" class="form-control" id="expires_in" name="expires_in" placeholder="Days" min="1">
              </div>
              <div class="col-sm-6">
                <p class="form-control-static text-muted">Days until expiration (leave empty for no expiration)</p>
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <div class="form-group">
            <div class="pull-right">
              <input type="hidden" name="type" value="v1-token-item-create">
              <button type="submit" class="btn btn-success" id="v1-token-create">Create V1 API Token</button>
            </div>
          </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <span id="thanks"></span>
    </div>
  </div>

  <h3>API Access</h3>
  <hr>

  <ul class="nav nav-tabs">
    <?php if ($canManage) { ?>
    <li class="active"><a href="#v0-tokens" data-toggle="tab">V0 API Tokens</a></li>
    <li><a href="#v1-tokens" data-toggle="tab">V1 API Tokens</a></li>
    <?php } else { ?>
    <li class="active"><a href="#v1-tokens" data-toggle="tab">V1 API Tokens</a></li>
    <?php } ?>
  </ul>

  <div class="tab-content">
    <?php if ($canManage) { ?>
    <!-- V0 API Tokens Tab -->
    <div class="tab-pane fade in active" id="v0-tokens">
      <br>
      <?php
        if (Session::get('api_token') === true) {
            echo "<script>\$('#thanks').html('<div class=\"alert alert-info\">The API token has been added.</div>');</script>";
            Session::forget('api_token');
        }
      ?>
      <table class="table table-bordered table-condensed">
        <tr>
          <th>User</th>
          <th>Auth</th>
          <th>Token Hash</th>
          <th>QR Code</th>
          <th>Description</th>
          <th>Disabled</th>
          <th>Remove</th>
        </tr>
        <?php
        foreach (ApiToken::all() as $api) {
            $user_details = $userlist->where('user_id', $api->user_id)->first();
            $api_disabled = $api->disabled == 1 ? 'checked' : '';
            $color = $user_details->auth_type == LegacyAuth::getType() ? '' : 'bgcolor="lightgrey"';

            echo '
        <tr id="' . $api->id . '" ' . $color . '>
          <td>' . htmlentities((string) $user_details->username) . '</td>
          <td>' . htmlentities((string) $user_details->auth_type) . '</td>
          <td>' . htmlentities((string) $api->token_hash) . '</td>
          <td><button class="btn btn-info btn-xs" data-toggle="modal" data-target="#display-qr" data-token_hash="' . htmlentities((string) $api->token_hash) . '"><i class="fa fa-qrcode"></i></button></td>
          <td>' . htmlspecialchars((string) $api->description) . '</td>
          <td><input type="checkbox" name="token-status" data-token_id="' . $api->id . '" data-off-text="No" data-on-text="Yes" data-on-color="danger" ' . $api_disabled . ' data-size="mini"></td>
          <td><button type="button" class="btn btn-danger btn-xs" id="' . $api->id . '" data-token_id="' . $api->id . '" data-toggle="modal" data-target="#confirm-delete">Delete</button></td>
        </tr>';
        }
        ?>
      </table>
      <center>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-token">Create API access token</button>
      </center>
    </div>
    <?php } ?>

    <!-- V1 API Tokens Tab -->
    <div class="tab-pane fade <?php echo $canManage ? '' : 'in active'; ?>" id="v1-tokens">
      <br>
      <span id="v1-thanks"></span>
      <table class="table table-bordered table-condensed">
        <tr>
          <?php if ($canManage) { ?>
          <th>User</th>
          <?php } ?>
          <th>Token Name</th>
          <th>Last Used</th>
          <th>Created</th>
          <th>Expires</th>
          <th>Actions</th>
        </tr>
        <?php
        $v1Tokens = $canManage
            ? \Laravel\Sanctum\PersonalAccessToken::with('tokenable')->get()
            : Auth::user()->tokens;

        foreach ($v1Tokens as $token) {
            $tokenUser = $canManage ? $token->tokenable : Auth::user();
            $expired = $token->expires_at && $token->expires_at->isPast();
            $expiresLabel = $token->expires_at
                ? ($expired ? '<span class="text-danger">' . $token->expires_at->diffForHumans() . '</span>' : $token->expires_at->diffForHumans())
                : 'Never';
            echo '
        <tr id="v1-' . $token->id . '"' . ($expired ? ' class="warning"' : '') . '>
          ' . ($canManage ? '<td>' . htmlentities((string) ($tokenUser->username ?? 'Unknown')) . '</td>' : '') . '
          <td>' . htmlentities((string) $token->name) . '</td>
          <td>' . ($token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never') . '</td>
          <td>' . $token->created_at->diffForHumans() . '</td>
          <td id="v1-expires-' . $token->id . '">' . $expiresLabel . '</td>
          <td><button type="button" class="btn btn-success btn-xs" data-v1-token-id="' . $token->id . '" data-toggle="modal" data-target="#renew-v1-token">Renew</button> <button type="button" class="btn btn-danger btn-xs" data-v1-token-id="' . $token->id . '" data-toggle="modal" data-target="#confirm-delete-v1">Delete</button></td>
        </tr>';
        }
        ?>
      </table>
      <center>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-v1-token">Create V1 API token</button>
      </center>
    </div>
  </div>

<script>
  // V0 token handlers
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
      },
      error:function(){
      }
    });
  });
  $('#confirm-delete').on('show.bs.modal', function(event) {
    token_id = $(event.relatedTarget).data('token_id');
    $("#token_id").val(token_id);
  });
  $('#display-qr').on('show.bs.modal', function(event) {
    token_hash = $(event.relatedTarget).data('token_hash');
    if ($('#qrcode').length) {
        $('#qrcode').empty();
    }
    new QRCode(document.getElementById("qrcode"), token_hash);
  });
  $('#token-removal').on("click", function(event) {
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
  $('#token-create').on("click", function(event) {
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
        $("#thanks").html('<div class="alert alert-info">An error occurred creating the token.</div>');
        $("#create-token").modal('hide');
      }
    });
  });
  $('#pass-gen').on("click", function(event) {
    event.preventDefault();
    token = $.password(32,false);
    $('#token').val(token);
  });

  // V1 token handlers
  $('#confirm-delete-v1').on('show.bs.modal', function(event) {
    var tokenId = $(event.relatedTarget).data('v1-token-id');
    $("#v1_token_id").val(tokenId);
  });
  $('#renew-v1-token').on('show.bs.modal', function(event) {
    var tokenId = $(event.relatedTarget).data('v1-token-id');
    $("#renew_v1_token_id").val(tokenId);
    $("#extend_days").val('');
  });
  $('#v1-token-renew-submit').on("click", function(event) {
    event.preventDefault();
    var tokenId = $("#renew_v1_token_id").val();
    $.ajax({
      type: "POST",
      url: "ajax_form.php",
      data: $('form.renew_v1_token_form').serialize(),
      dataType: "json",
      success: function(data){
        if (data.status === 'ok') {
          $("#v1-thanks").html('<div class="alert alert-info">' + data.message + '</div>');
          $("#v1-expires-" + tokenId).html($('<span>').text(data.expires_at).html());
          $("#v1-" + tokenId).removeClass('warning');
        } else {
          $("#v1-thanks").html('<div class="alert alert-danger">' + data.message + '</div>');
        }
        $("#renew-v1-token").modal('hide');
      },
      error: function(){
        $("#v1-thanks").html('<div class="alert alert-danger">An error occurred renewing the token.</div>');
        $("#renew-v1-token").modal('hide');
      }
    });
  });
  $('#v1-token-removal').on("click", function(event) {
    event.preventDefault();
    var tokenId = $("#v1_token_id").val();
    $.ajax({
      type: "POST",
      url: "ajax_form.php",
      data: $('form.remove_v1_token_form').serialize(),
      dataType: "json",
      success: function(data){
        if (data.status === 'ok') {
          $("#v1-thanks").html('<div class="alert alert-info">' + data.message + '</div>');
          $("#v1-" + tokenId).remove();
        } else {
          $("#v1-thanks").html('<div class="alert alert-danger">' + data.message + '</div>');
        }
        $("#confirm-delete-v1").modal('hide');
      },
      error: function(){
        $("#v1-thanks").html('<div class="alert alert-danger">An error occurred removing the token.</div>');
        $("#confirm-delete-v1").modal('hide');
      }
    });
  });
  $('#v1-token-create').on("click", function(event) {
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "ajax_form.php",
      data: $('form.create_v1_token_form').serialize(),
      dataType: "json",
      success: function(data){
        $("#create-v1-token").modal('hide');
        if (data.status === 'ok') {
          $("#v1-thanks").html(
            '<div class="alert alert-warning">' +
            '<strong><i class="fa fa-exclamation-triangle"></i> Save this token now!</strong> ' +
            'It cannot be retrieved after leaving or reloading this page.<br><br>' +
            '<code style="font-size: 1.2em; user-select: all; display: block; padding: 10px; margin-top: 5px; word-break: break-all;">' + $('<span>').text(data.token).html() + '</code>' +
            '</div>'
          );
          // Append new row to table
          var canManage = <?php echo $canManage ? 'true' : 'false'; ?>;
          var row = '<tr id="v1-' + data.token_id + '">';
          if (canManage) {
            row += '<td>' + $('<span>').text(data.username).html() + '</td>';
          }
          row += '<td>' + $('<span>').text(data.token_name).html() + '</td>';
          row += '<td>Never</td>';
          row += '<td>' + $('<span>').text(data.created_at).html() + '</td>';
          row += '<td id="v1-expires-' + data.token_id + '">' + $('<span>').text(data.expires_at).html() + '</td>';
          row += '<td><button type="button" class="btn btn-success btn-xs" data-v1-token-id="' + data.token_id + '" data-toggle="modal" data-target="#renew-v1-token">Renew</button> <button type="button" class="btn btn-danger btn-xs" data-v1-token-id="' + data.token_id + '" data-toggle="modal" data-target="#confirm-delete-v1">Delete</button></td>';
          row += '</tr>';
          $('#v1-tokens table tr:last').after(row);
        } else {
          $("#v1-thanks").html('<div class="alert alert-danger">' + $('<span>').text(data.message).html() + '</div>');
        }
      },
      error: function(){
        $("#v1-thanks").html('<div class="alert alert-danger">An error occurred creating the token.</div>');
        $("#create-v1-token").modal('hide');
      }
    });
  });
</script>

    <?php
} else {
    include 'includes/html/error-no-perm.inc.php';
}//end if
