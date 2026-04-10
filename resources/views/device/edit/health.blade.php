@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="health" />

        <form class="form-inline">
            <table class="table table-striped table-condensed table-bordered">
                <tr>
                    <th>Class</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Current</th>
                    <th class="col-sm-1">High</th>
                    <th class="col-sm-1">High warn</th>
                    <th class="col-sm-1">Low warn</th>
                    <th class="col-sm-1">Low</th>
                    <th class="col-sm-2">Alerts</th>
                    <th></th>
                </tr>
                @foreach ($sensors as $sensor)
                    <tr>
                        <td>{{ $sensor->sensor_class }}</td>
                        <td>{{ $sensor->sensor_type }}</td>
                        <td style="white-space: nowrap">{{ $sensor->sensor_descr }}</td>
                        <td>
                            {{ $sensor->sensor_current }}
                            @if ($sensor->sensor_class === 'temperature')
                                °C
                            @endif
                        </td>
                        <td>
                            <div class="form-group has-feedback">
                                <input type="text"
                                       class="form-control col-sm-1 input-sm sensor"
                                       id="high-{{ $sensor->device_id }}"
                                       data-device_id="{{ $sensor->device_id }}"
                                       data-value_type="sensor_limit"
                                       data-sensor_id="{{ $sensor->sensor_id }}"
                                       data-update-url="{{ route('device.edit.health.sensor.update', [$device, $sensor]) }}"
                                       value="{{ $sensor->sensor_limit }}">
                            </div>
                        </td>
                        <td>
                            <div class="form-group has-feedback">
                                <input type="text"
                                       class="form-control col-sm-1 input-sm sensor"
                                       id="high-{{ $sensor->device_id }}-warn"
                                       data-device_id="{{ $sensor->device_id }}"
                                       data-value_type="sensor_limit_warn"
                                       data-sensor_id="{{ $sensor->sensor_id }}"
                                       data-update-url="{{ route('device.edit.health.sensor.update', [$device, $sensor]) }}"
                                       value="{{ $sensor->sensor_limit_warn }}">
                            </div>
                        </td>
                        <td>
                            <div class="form-group has-feedback">
                                <input type="text"
                                       class="form-control col-sm-1 input-sm sensor"
                                       id="low-{{ $sensor->device_id }}-warn"
                                       data-device_id="{{ $sensor->device_id }}"
                                       data-value_type="sensor_limit_low_warn"
                                       data-sensor_id="{{ $sensor->sensor_id }}"
                                       data-update-url="{{ route('device.edit.health.sensor.update', [$device, $sensor]) }}"
                                       value="{{ $sensor->sensor_limit_low_warn }}">
                            </div>
                        </td>
                        <td>
                            <div class="form-group has-feedback">
                                <input type="text"
                                       class="form-control input-sm sensor"
                                       id="low-{{ $sensor->device_id }}"
                                       data-device_id="{{ $sensor->device_id }}"
                                       data-value_type="sensor_limit_low"
                                       data-sensor_id="{{ $sensor->sensor_id }}"
                                       data-update-url="{{ route('device.edit.health.sensor.update', [$device, $sensor]) }}"
                                       value="{{ $sensor->sensor_limit_low }}">
                            </div>
                        </td>
                        <td>
                            <input type="checkbox"
                                   name="alert-status"
                                   data-device_id="{{ $sensor->device_id }}"
                                   data-sensor_id="{{ $sensor->sensor_id }}"
                                   data-sensor_desc="{{ $sensor->sensor_descr }}"
                                   data-alert-url="{{ route('device.edit.health.sensor.alert', [$device, $sensor]) }}"
                                   {{ $sensor->sensor_alert == 1 ? 'checked' : '' }}>
                        </td>
                        <td>
                            <a type="button"
                               class="btn btn-danger btn-sm {{ $sensor->sensor_custom === 'Yes' ? '' : 'disabled' }} remove-custom"
                               id="remove-custom"
                               name="remove-custom"
                               data-sensor_id="{{ $sensor->sensor_id }}"
                               data-alert-url="{{ route('device.edit.health.sensor.alert', [$device, $sensor]) }}">Reset</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </form>

        <form id="alert-reset">
            @csrf
            @foreach ($sensors as $sensor)
                <input type="hidden" name="sensor_id[]" value="{{ $sensor->sensor_id }}">
            @endforeach
            <button id="newThread" class="btn btn-primary btn-sm" type="submit">Reset values</button>
        </form>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        $('#newThread').on('click', function(e){
            e.preventDefault(); // preventing default click action

            var form = $('#alert-reset');

            $.ajax({
                type: 'POST',
                url: '{{ route('device.edit.health.sensor.reset', $device) }}',
                data: form.serialize(),
                dataType: "json",
                success: function(data){
                    if (data.status === 'ok') {
                        toastr.success(data.message);
                        setTimeout(function() {
                            location.reload(true);
                        }, 2000);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error:function(data){
                    toastr.error(data.message);
                }
            });
        });

        $('.sensor').on('focusin', function(){
            $(this).data('val', $(this).val());
        });

        $('.sensor').on('blur keyup', function(e) {
            if (e.type === 'keyup' && e.keyCode !== 13) return;
            var prev = $(this).data('val');
            var data = $(this).val();
            if (prev === data) return;

            var device_id = $(this).data('device_id');
            var sensor_id = $(this).data('sensor_id');
            var value_type = $(this).data('value_type');
            var $this = $(this);
            $.ajax({
                type: 'POST',
                url: $(this).data('update-url'),
                data: {
                    device_id: device_id,
                    data: data,
                    value_type: value_type,
                    _token: '{{ csrf_token() }}'
                },
                dataType: "json",
                success: function(data){
                    if (data.status === 'ok') {
                        $('.remove-custom[data-sensor_id=' + sensor_id + ']').removeClass('disabled');
                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error:function(data){
                    toastr.error(data.message);
                }
            });
        });

        $('[name="alert-status"]').bootstrapSwitch('offColor','danger');
        $('input[name="alert-status"]').on('switchChange.bootstrapSwitch',  function(event, state) {
            event.preventDefault();
            var $this = $(this);
            var device_id = $(this).data('device_id');
            var sensor_id = $(this).data('sensor_id');
            var sensor_desc = $(this).data('sensor_desc');
            $.ajax({
                type: 'POST',
                url: $(this).data('alert-url'),
                data: {
                    device_id: device_id,
                    sensor_desc: sensor_desc,
                    state: state ? 1 : 0,
                    _token: '{{ csrf_token() }}'
                },
                dataType: "json",
                success: function(data){
                    if (data.status !== 'error') {
                        if (data.status === 'ok') {
                            toastr.success(data.message);
                        } else {
                            toastr.info(data.message);
                        }
                    } else {
                        toastr.error(data.message);
                    }
                },
                error:function(data){
                    toastr.error(data.message);
                }
            });
        });

        $('[name="remove-custom"]').on('click', function(event) {
            event.preventDefault();
            var $this = $(this);
            var sensor_id = $(this).data('sensor_id');
            $.ajax({
                type: 'POST',
                url: $(this).data('alert-url'),
                data: {
                    sub_type: "remove-custom",
                    _token: '{{ csrf_token() }}'
                },
                dataType: "json",
                success: function(data){
                    toastr.success(data.message);
                    $this.addClass('disabled');
                },
                error:function(data){
                    toastr.error(data.message);
                }
            });
        });
    </script>
@endpush

