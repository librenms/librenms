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

    ?>

<div class="modal fade bs-example-modal-sm" id="delete-maintenance" tabindex="-1" role="dialog" aria-labelledby="delete" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Create">Delete maintenance</h5>
            </div>
            <div class="modal-body">
                <p>If you would like to remove this maintenance then please click Delete.</p>
            </div>
            <div class="modal-footer">
                <form method="post" role="form" id="sched-del" class="form-horizontal">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger danger" id="sched-maintenance-removal" data-target="sched-maintenance-removal">Delete</button>
                    <input type="hidden" name="del_schedule_id" id="del_schedule_id">
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$('#sched-maintenance-removal').on("click", function(e) {
    e.preventDefault();
    $.ajax({
        type: "DELETE",
        url: route('alert-schedule.destroy', {alert_schedule: $('#del_schedule_id').val()}),
        dataType: "json",
        success: function(data){
            $("#message").html('<div class="alert alert-info" id="schedulemsg"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+data.message+'</div>');
            window.setTimeout(function() { $('#schedulemsg').fadeOut().slideUp(); } , 5000);
            $("#delete-maintenance").modal('hide');
            $("#alert-schedule").bootgrid('reload');
        },
        error: function(jqXHR){
            $("#delete-maintenance").modal('hide');
            toastr.error((jqXHR.responseJSON && jqXHR.responseJSON.message) || 'An error occurred.');
        }
    });
});

</script>
