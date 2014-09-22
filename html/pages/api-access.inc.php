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

if ($_SESSION['userlevel'] == '10')
{
?>
  <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="Delete" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">          
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title" id="Delete">Confirm Delete</h5>
        </div>
        <div class="modal-body">
          <p>If you would like to remove the API token for then please click Delete.</p>
        </div>        
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <a href="#" class="btn btn-danger danger">Delete</a>
        </div>
      </div>
    </div>
  </div>
<?php
  echo('
  <div class="row">
    <div class="col-sm-6">
      <table class="table table-bordered table-condensed">
        <tr>
          <th>User</th>
          <th>Token Hash</th>
          <th>Description</th>
          <th>Disabled</th>
          <th>Remove</th>
        </tr>
');

  foreach (dbFetchRows("SELECT `AT`.*,`U`.`username` FROM `api_tokens` AS AT JOIN users AS U ON AT.user_id=U.user_id ORDER BY AT.user_id") as $api)
  {
    if($api['disabled'] == '1')
    {
      $api_disabled = 'checked';
    }
    else
    {
      $api_disabled = '';
    }
    echo('
        <tr>
          <td>'.$api['username'].'</td>
          <td>'.$api['token_hash'].'</td>
          <td>'.$api['description'].'</td>
          <td><input type="checkbox" name="token-status" data-token_id="'.$api['id'].'" data-off-text="No" data-on-text="Yes" data-on-color="danger" '.$api_disabled.' data-size="mini"></td>
          <td><a href="" class="btn btn-primary btn-xs" role="button" data-toggle="modal" data-target="#confirm-delete">Delete</a></td>
        </tr>
');
  }

  echo('
      </table>
    </div>
  </div>
');
?>
<script>
  $("[name='token-status']").bootstrapSwitch('offColor','success');
  $('input[name="token-status"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var $this = $(this);
    var token_id = $(this).data("token_id");
    $.ajax({
      type: 'POST',
      url: '/ajax_form.php',
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
  $('#confirm-delete').on('show.bs.modal', function(e) {
    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));        
    $('.debug-url').html('Delete URL: <strong>' + $(this).find('.danger').attr('href') + '</strong>');
  });
</script>

<?php
} else {
  include("includes/error-no-perm.inc.php");
}

?>
