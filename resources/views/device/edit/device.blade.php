@extends('layouts.librenmsv1')

@section('content')
    @include('device.edit.maintenance')

    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <!-- Bootstrap 3 doesn't support mediaqueries for text aligns (e.g. text-md-left), which makes these buttons stagger on sm or xs screens -->
            <div class="col-md-6 col-md-offset-2 tw:justify-between tw:flex">
                <form id="delete_host" name="delete_host" method="post" action="delhost/" role="form" class="tw:inline-block">
                    @csrf
                    <input type="hidden" name="id" value="{{ $device->device_id }}">
                    <button type="submit" class="btn btn-danger" name="Submit"><i class="fa fa-trash"></i> Delete device</button>
                </form>

                @if(LibrenmsConfig::get('enable_clear_discovery') && ! $device->snmp_disable)
                    <button type="submit" id="rediscover" data-device_id="{{ $device->device_id }}"
                            class="btn btn-primary" name="rediscover" title="Schedule the device for immediate rediscovery by the poller">
                        <i class="fa fa-retweet"></i> Rediscover device
                    </button>
                @endif
            </div>
        </div>
        <br>

        <form id="edit" name="edit" method="post" action="{{ route('device.edit.update', [$device->device_id]) }}" role="form" class="form-horizontal">
            @method('PUT')
            @csrf
            <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="Change the hostname used for name resolution" >
                <label for="edit-hostname-input" class="col-sm-2 control-label" >Hostname / IP</label>
                <div class="col-sm-6">
                    <input type="text" id="edit-hostname-input" name="hostname" class="form-control" disabled value="{{ old('hostname', $device->hostname) }}" />
                </div>
                <div class="col-sm-2">
                    <button type="button" name="hostname-edit-button" id="hostname-edit-button" class="btn btn-danger" onclick="toggleHostnameEdit()"> <i class="fa fa-pencil"></i> </button>
                </div>
            </div>

            <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="Display Name for this device.  Keep short. Available placeholders: hostname, sysName, sysName_fallback, ip (e.g. '@{{ $sysName }}')" >
                <label for="edit-display-input" class="col-sm-2 control-label" >Display Name</label>
                <div class="col-sm-6">
                    <input type="text" id="edit-display-input" name="display" class="form-control" placeholder="System Default" value="{{ old('display', $device->display) }}">
                </div>
            </div>

            <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="Use this IP instead of resolved one for polling" >
                <label for="edit-overwrite_ip-input" class="col-sm-2 control-label text-danger" >Overwrite IP (do not use)</label>
                <div class="col-sm-6">
                    <input type="text" id="edit-overwrite_ip-input" name="overwrite_ip" class="form-control" value="{{ old('overwrite_ip', $device->overwrite_ip) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="descr" class="col-sm-2 control-label">Description</label>
                <div class="col-sm-6">
                    <textarea id="descr" name="purpose" class="form-control">{{ old('purpose', $device->purpose) }}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="type" class="col-sm-2 control-label">Type</label>
                <div class="col-sm-6">
                    <select id="type" name="type" class="form-control">
                        @foreach($types as $type => $text)
                            <option value="{{ $type }}" {{ old('type', $device->type) == $type ? 'selected' : '' }}>
                                {{ ucfirst($text) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="sysLocation" class="col-sm-2 control-label">Override sysLocation</label>
                <div class="col-sm-6">
                    <input onChange="edit.sysLocation.disabled=!edit.override_sysLocation.checked; edit.sysLocation.select()"
                           type="checkbox" name="override_sysLocation" data-size="small"
                            {{ old('override_sysLocation', $device->override_sysLocation) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="form-group" title="To set coordinates, include [latitude,longitude]">
                <div class="col-sm-2"></div>
                <div class="col-sm-6">
                    <input id="sysLocation" name="sysLocation" class="form-control"
                           {{ old('override_sysLocation', $device->override_sysLocation) ? '' : 'disabled' }}
                             value="{{ old('sysLocation', $device->location?->location) }}" />
                </div>
            </div>

            <div class="form-group">
                <label for="override_sysContact" class="col-sm-2 control-label">Override sysContact</label>
                <div class="col-sm-6">
                    <input onChange="edit.override_sysContact_string.disabled=!edit.override_sysContact.checked"
                           type="checkbox" id="override_sysContact" name="override_sysContact" data-size="small"
                            {{ old('override_sysContact', $override_sysContact_bool) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                </div>
                <div class="col-sm-6">
                    <input id="override_sysContact_string" class="form-control" name="override_sysContact_string" size="32"
                           {{ old('override_sysContact', $override_sysContact_bool) ? '' : 'disabled' }}
                           value="{{ old('override_sysContact_string', $override_sysContact_string) }}"
                    />
                </div>
            </div>

            <div class="form-group">
                <label for="parent_id" class="col-sm-2 control-label">This device depends on</label>
                <div class="col-sm-6">
                    <select multiple name="parent_id[]" id="parent_id" class="form-control" style="width: 100%">
                        <option value="0" {{ empty($parents) ? 'selected' : '' }}>None</option>
                        @foreach ($devices as $dev)
                            <option value="{{ $dev->device_id }}" {{ $parents->contains($dev->device_id) ? 'selected' : '' }}>
                                {{ $dev->hostname }} ({{ $dev->sysName }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @config('distributed_poller')
            <div class="form-group">
                <label for="poller_group" class="col-sm-2 control-label">Poller Group</label>
                <div class="col-sm-6">
                    <select name="poller_group" id="poller_group" class="form-control input-sm">
                        <option value="0">General{{$default_poller_group == 0 ? ' (default poller)' : ''}}</option>
                        @foreach($poller_groups as $group_id => $group_name)
                            <option value="{{ $group_id }}" {{ old('poller_group', $device->poller_group) == $group_id ? 'selected' : '' }}>
                                {{ $group_name }}{{ $default_poller_group == $group_id ? ' (default poller)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endconfig

            <div class="form-group">
                <label for="disabled" class="col-sm-2 control-label">Disable polling and alerting</label>
                <div class="col-sm-6">
                    <input name="disabled" type="checkbox" id="disabled" value="1" data-size="small"
                       {{ old('disabled', $device->disabled) ? 'checked' : '' }}
                    />
                </div>
            </div>

            <div class="form-group">
                <label for="maintenance" class="col-sm-2 control-label"></label>
                <div class="col-sm-6">
                    <button type="button"
                            id="maintenance"
                            name="maintenance"
                            data-device_id="{{ $device->device_id }}"
                            data-maintenance-id="{{ $exclusive_maintenance_id }}"
                            {{ $maintenance && ! $exclusive_maintenance_id ? 'disabled' : '' }}
                            class="btn {{ $maintenance ? 'btn-warning' : 'btn-success' }}"
                            >
                        <i class="fa fa-wrench"></i> {{ $maintenance ? 'Device under Maintenance' : 'Maintenance Mode' }}
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="disable_notify" class="col-sm-2 control-label">Disable alerting</label>
                <div class="col-sm-6">
                    <input id="disable_notify" type="checkbox" name="disable_notify" data-size="small"
                       {{ old('disable_notify', $device->disable_notify) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="form-group">
                <label for="ignore" class="col-sm-2 control-label" title="Tag device to ignore alerts. Alert checks will still run.
However, ignore tag can be read in alert rules.
If `devices.ignore = 0` or `macros.device = 1` condition is is set and ignore alert tag is on, the alert rule won't match.">Ignore alert tag</label>
                <div class="col-sm-6">
                    <input name="ignore" type="checkbox" id="ignore" value="1" data-size="small"
                       {{ old('ignore', $device->ignore) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="form-group">
                <label for="ignore_status" class="col-sm-2 control-label" title="Tag device to ignore Status. It will always be shown as online.">Ignore Device Status</label>
                <div class="col-sm-6">
                    <input name="ignore_status" type="checkbox" id="ignore_status" value="1" data-size="small"
                       {{ old('ignore_status', $device->ignore_status) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-1 col-md-offset-2">
                    <button type="submit" name="Submit"  class="btn btn-default"><i class="fa fa-check"></i> Save</button>
                </div>
            </div>
        </form>
        <br />
        <div class="panel panel-default">
            <div class="panel-heading">
                @if($rrd_num)
                Size on Disk: <b>{{ $rrd_size }}</b> in <b>{{ $rrd_num }}</b> RRD files |
                @endif
                Last polled: <b>{{ $device->last_polled }}</b>
                @if($device->last_discovered)
                    | Last discovered: <b>{{ $device->last_discovered }}</b>
                @endif
            </div>
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        $('[type="checkbox"]').bootstrapSwitch('offColor', 'danger');

        $("#maintenance").on("click", function() {
            $("#device_maintenance_modal").modal('show');
        });
        $("#rediscover").on("click", function() {
            var device_id = $(this).data("device_id");
            $.ajax({
                type: 'POST',
                url: '{{ route('device.rediscover', [$device->device_id]) }}',
                data: {

                },
                dataType: "json",
                success: function(data){
                    if(data['status'] === 'ok') {
                        toastr.success(data['message']);
                    } else {
                        toastr.error(data['message']);
                    }
                },
                error:function(){
                    toastr.error('An error occurred setting this device to be rediscovered');
                }
            });
        });

        function toggleHostnameEdit() {
            document.getElementById('edit-hostname-input').disabled = ! document.getElementById('edit-hostname-input').disabled;
        }
        $('#parent_id').select2({
            width: 'resolve'
        });
    </script>
@endpush
