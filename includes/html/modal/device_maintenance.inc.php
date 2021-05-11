<?php
/**
 * device_maintenance.inc.php
 *
 * LibreNMS device maintenance modal
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */
if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

$hour_steps = range(0, 23, 1);
$minute_steps = [0, 30];
$exclude_durations = ['0:00'];

$maintenance_duration_list = [];
foreach ($hour_steps as $hour) {
    foreach ($minute_steps as $min) {
        if (empty($hour) && empty($min)) {
            continue;
        }
        $str_hour = $hour;
        $str_min = $min < 10 ? '0' . $min : $min;

        $duration = $str_hour . ':' . $str_min;

        if (in_array($duration, $exclude_durations)) {
            continue;
        }
        $maintenance_duration_list[] = $duration;
    }
}
?>
<div class="modal fade" id="device_maintenance_modal" tabindex="-1" role="dialog" aria-labelledby="device_edit" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="search_alert_rule_list">Device Maintenance</h5>
            </div>
            <div class="modal-body">
                <form method="post" role="form" id="sched-form" class="form-horizontal schedule-maintenance-form">
                    <?php echo csrf_field() ?>
                    <div class="form-group">
                        <label for="notes" class="col-sm-4 control-label">Notes: </label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="notes" name="notes" placeholder="Maintenance notes"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="duration" class="col-sm-4 control-label">Duration: </label>
                        <div class="col-sm-8">
                            <select name='duration' id='duration' class='form-control input-sm'>
                                <?php foreach ($maintenance_duration_list as $dur) { ?>
                                <option value='<?=$dur?>'><?=$dur?>h</option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="maintenance-submit" class="col-sm-4 control-label"></label>
                        <div class="col-sm-8">
                            <button type="submit" id="maintenance-submit" data-device_id="<?php echo $device['device_id']; ?>" <?php echo \LibreNMS\Alert\AlertUtil::isMaintenance($device['device_id']) ? 'disabled class="btn btn-warning"' : 'class="btn btn-success"'?> name="maintenance-submit">Start Maintenance</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $("#maintenance-submit").on("click", function() {
        var device_id = $(this).data("device_id");
        var title = '<?=\LibreNMS\Util\Clean::html($device['hostname'], []); ?>';
        var notes = $('#notes').val();
        var recurring = 0;
        var start = '<?=date('Y-m-d H:i:00'); ?>';
        var duration = $('#duration').val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "schedule-maintenance",
                    sub_type: 'new-maintenance',
                    title: title,
                    notes: notes,
                    recurring: recurring,
                    start: start,
                    duration: duration,
                    maps: [device_id]
                  },
            dataType: "json",
            success: function(data){
                if(data['status'] == 'ok') {
                    toastr.success(data['message']);
                } else {
                    toastr.error(data['message']);
                }
            },
            error:function(){
                toastr.error('An error occured setting this device into maintenance mode');
            }
        });
    });
</script>
