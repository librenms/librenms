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

if (!Auth::user()->hasGlobalAdmin()) {
    echo ('ERROR: You need to be admin');
} else {
?>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="Delete" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Delete">Confirm Delete</h5>
            </div>
            <div class="modal-body">
                <p>If you would like to remove the Poller Group then please click Delete.</p>
            </div>
            <div class="modal-footer">
                <form role="form" class="remove_group_form">
                    <?php echo csrf_field() ?>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger danger" id="group-removal" data-target="group-removal">Delete</button>
                    <input type="hidden" name="group_id" id="group_id" value="">
                    <input type="hidden" name="type" id="type" value="poller-group-remove">
                    <input type="hidden" name="confirm" id="confirm" value="yes">
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bs-example-modal-lg" id="poller-groups" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="Create">Poller Groups</h4>
            </div>
            <div class="modal-body">
                <form method="post" role="form" id="poller_groups" class="form-horizontal poller-groups-form">
                <?php echo csrf_field() ?>
                <input type="hidden" name="group_id" id="group_id" value="">
                <div class="row">
                    <div class="col-md-12">
                        <span id="response"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="group_name" class="col-sm-3 control-label">Group Name:</label>
                            <div class="col-sm-9">
                                <input type="input" class="form-control" id="group_name" name="group_name" placeholder="Group Name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descr" class="col-sm-3 control-label">Description:</label>
                            <div class="col-sm-9">
                                <input type="input" class="form-control" id="descr" name="descr" placeholder="Description">
                            </div>
                        </div>
                        <div class="form-group">
                             <div class="col-sm-offset-3 col-sm-9">
                                 <button type="submit" class="btn btn-primary btn-sm" id="create-group" name="create-group">Add Poller Group</button>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
<script>

$('#confirm-delete').on('show.bs.modal', function(e) {
    group_id = $(e.relatedTarget).data('group_id');
    $("#group_id").val(group_id);
});

$('#group-removal').click('', function(e) {
    e.preventDefault();
    group_id = $("#group_id").val();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: $('form.remove_group_form').serialize() ,
        success: function(msg) {
            $("#thanks").html('<div class="alert alert-info">'+msg+'</div>');
            $("#confirm-delete").modal('hide');
            $("#"+group_id).remove();
        },
        error: function() {
            $("#thanks").html('<div class="alert alert-info">An error occurred removing the token.</div>');
            $("#confirm-delete").modal('hide');
        }
    });
});

$('#poller-groups').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var group_id = button.data('group_id');
    $('#group_id').val(group_id);
    if(group_id != '') {
        $('#group_id').val(group_id);
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "parse-poller-groups", group_id: group_id },
            dataType: "json",
            success: function(output) {
                $('#group_name').val(output['group_name']);
                $('#descr').val(output['descr']);
            }
        });
    }
});

$('#create-group').click('', function(e) {
    e.preventDefault();
    var group_name = $("#group_name").val();
    var descr = $("#descr").val();
    var group_id = $('#group_id').val();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: { type: "poller-groups", group_name: group_name, descr: descr, group_id: group_id },
        dataType: "html",
        success: function(msg){
            if(msg.indexOf("ERROR:") <= -1) {
                $("#message").html('<div class="alert alert-info">'+msg+'</div>');
                $("#poller-groups").modal('hide');
                setTimeout(function() {
                    location.reload(1);
                }, 1000);
            } else {
                $("#error").html('<div class="alert alert-info">'+msg+'</div>');
            }
        },
        error: function(){
            $("#error").html('<div class="alert alert-info">An error occurred.</div>');
        }
    });
});

</script>

<?php
}
