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

use App\Facades\DeviceCache;

$pagetitle[] = 'Notes';
$port_id_notes = 'port_id_notes:' . $port['port_id'];
$device = DeviceCache::getPrimary();
?>

<form class="form-horizontal" action="" method="post">
    <?php echo csrf_field() ?>
    <h3>Port Notes</h3>
    <hr>
    <div class="form-group">
        <div class="col-sm-10">
            <textarea class="form-control" rows="6" name="notes" id="port-notes"><?php
            echo htmlentities($device->getAttrib($port_id_notes) ?? ''); ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10">
            <?php
            echo '
            <button type="submit" name="btn-update-notes" id="btn-update-notes" class="btn btn-primary">Submit</button>
            ';
            ?>
        </div>
    </div>
</form>
<script>
$("[name='btn-update-notes']").on('click', function(event) {
    event.preventDefault();
    var $this = $(this);
    var device_id = "<?php echo $device->device_id; ?>";
    var port_id_notes = "<?php echo $port_id_notes; ?>";
    var notes = $("#port-notes").val();
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "update-port-notes", notes: notes, port_id_notes: port_id_notes, device_id: device_id },
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
