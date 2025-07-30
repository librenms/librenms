<div class="modal fade" id="device_maintenance_modal" tabindex="-1" role="dialog" aria-labelledby="device_edit" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="search_alert_rule_list">Device Maintenance</h5>
            </div>
            <div class="modal-body">
                <form method="post" role="form" id="sched-form" class="form-horizontal schedule-maintenance-form">
                    @csrf
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
                                @foreach($maintenance_duration_list as $dur)
                                <option value='{{ $dur }}'>{{ $dur }}h</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="behavior" class="col-sm-4 control-label">Behavior: </label>
                        <div class="col-sm-8">
                            <select name='behavior' id='behavior' class='form-control input-sm'>
                                @foreach($maintenance_behaviors as $behavior)
                                <option value='{{ $behavior->value }}' {{ $default_maintenance_behavior === $behavior ? 'selected' : '' }}>
                                    {{ $behavior->descr() }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="maintenance-submit" class="col-sm-4 control-label"></label>
                        <div class="col-sm-8">
                            <button
                                type="button"
                                id="maintenance-submit"
                                data-device_id="{{ $device->device_id }}"
                                {{ $maintenance ? 'disabled' : '' }}
                                class="btn {{ $maintenance ? 'btn-warning' : 'btn-success' }}"
                                name="maintenance-submit">
                                Start Maintenance
                            </button>
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
        var title = '{{ $device->displayName() }}';
        var notes = $('#notes').val();
        var recurring = 0;
        var start = '{{ date('Y-m-d H:i:00') }}';
        var duration = $('#duration').val();
        var behavior = $('#behavior').val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "schedule-maintenance",
                sub_type: 'new-maintenance',
                title: title,
                notes: notes,
                behavior: behavior,
                recurring: recurring,
                start: start,
                duration: duration,
                maps: [device_id]
            },
            dataType: "json",
            success: function(data){
                if(data['status'] === 'ok') {
                    toastr.success(data['message']);
                    location.reload();
                } else {
                    toastr.error(data['message']);
                }
            },
            error:function(){
                toastr.error('An error occurred setting this device into maintenance mode');
            }
        });
    });
</script>
