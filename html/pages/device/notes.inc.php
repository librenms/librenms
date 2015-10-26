<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
$data = dbFetchRow("SELECT `notes` FROM `devices` WHERE device_id = ?", array(
    $device['device_id']
));
?>

<form class="form-horizontal" action="" method="post">
    <h3>Device Notes</h3>
    <hr>
    <div class="form-group">
        <div class="col-sm-12">
            <textarea class="form-control" rows="6" name="notes" id="device-notes"><?php echo htmlentities($data['notes']); ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-md-1 col-md-offset-5">
            <?php
            echo '
                <button type="submit" name="btn-update-notes" id="btn-update-notes" class="btn btn-default" data-device_id="' . $device['device_id'] . '"><i class="fa fa-check"></i> Save</button>
            ';
            ?>
        </div>
    </div>
</form>
<script>
$("[name='btn-update-notes']").on('click', function(event) {
    event.preventDefault();
    var $this = $(this);
    var device_id = $(this).data("device_id");
    var notes = $("#device-notes").val();
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "update-notes", notes: notes, device_id: device_id},
        dataType: "html",
        success: function(data){
            toastr.success('Saved');
        },
        error:function(){
            toastr.error('Error');
        }
    });
});
</script>
