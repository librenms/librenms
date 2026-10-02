@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="wireless-sensors" />

        <form class="form-inline">
            <table class="table table-striped table-condensed table-bordered">
                <tr>
                    <th>{{ __('Class') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Current') }}</th>
                    <th class="col-sm-1">{{ __('High Limit') }}</th>
                    <th class="col-sm-1">{{ __('High warn') }}</th>
                    <th class="col-sm-1">{{ __('Low warn') }}</th>
                    <th class="col-sm-1">{{ __('Low Limit') }}</th>
                    <th class="col-sm-2">{{ __('Alerts') }}</th>
                    <th></th>
                </tr>
                @foreach ($sensors as $sensor)
                    <tr>
                        <td>{{ $sensor->classDescr() }}</td>
                        <td>{{ $sensor->sensor_type }}</td>
                        <td style="white-space: nowrap">{{ $sensor->sensor_descr }}</td>
                        <td>{{ $sensor->formatValue() }}</td>
                        @foreach (['sensor_limit', 'sensor_limit_warn', 'sensor_limit_low_warn', 'sensor_limit_low'] as $valueType)
                            <td>
                                <div class="form-group has-feedback">
                                    <input type="text"
                                           class="form-control input-sm wireless-sensor-limit"
                                           data-field="{{ $valueType }}"
                                           data-sensor_id="{{ $sensor->sensor_id }}"
                                           data-update-url="{{ route('device.edit.wireless-sensors.update', [$device, $sensor]) }}"
                                           value="{{ $sensor->$valueType }}">
                                </div>
                            </td>
                        @endforeach
                        <td>
                            <input type="checkbox"
                                   name="alert-status"
                                   data-update-url="{{ route('device.edit.wireless-sensors.update', [$device, $sensor]) }}"
                                   @checked($sensor->sensor_alert)>
                        </td>
                        <td>
                            <a type="button"
                               class="btn btn-danger btn-sm remove-custom {{ $sensor->sensor_custom === 'Yes' ? '' : 'disabled' }}"
                               data-sensor_id="{{ $sensor->sensor_id }}"
                               data-update-url="{{ route('device.edit.wireless-sensors.update', [$device, $sensor]) }}">{{ __('Reset') }}</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </form>

        <button id="reset-all-custom" class="btn btn-primary btn-sm" type="button">{{ __('Reset values') }}</button>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        function wirelessSensorPost(url, data) {
            data._token = '{{ csrf_token() }}';

            return $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json'
            }).fail(function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? '{{ __('Request failed') }}');
            });
        }

        function clearLimits(sensorId) {
            $('.wireless-sensor-limit[data-sensor_id=' + sensorId + ']').val('');
        }

        $('#reset-all-custom').on('click', function () {
            wirelessSensorPost('{{ route('device.edit.wireless-sensors.reset', $device) }}', {}).done(function (data) {
                toastr.success(data.message);
                $('.remove-custom:not(.disabled)').each(function () {
                    clearLimits($(this).data('sensor_id'));
                }).addClass('disabled');
            });
        });

        $('.wireless-sensor-limit').on('focusin', function () {
            $(this).data('val', $(this).val());
        }).on('blur keyup', function (e) {
            if (e.type === 'keyup' && e.keyCode !== 13) return;
            var $this = $(this);
            var value = $this.val();
            if ($this.data('val') === value) return;

            var data = {};
            data[$this.data('field')] = value;
            wirelessSensorPost($this.data('update-url'), data).done(function (data) {
                if (data.status === 'ok') {
                    $this.data('val', value);
                    $('.remove-custom[data-sensor_id=' + $this.data('sensor_id') + ']').removeClass('disabled');
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            });
        });

        $('[name="alert-status"]').bootstrapSwitch('offColor', 'danger')
            .on('switchChange.bootstrapSwitch', function (event, state) {
                wirelessSensorPost($(this).data('update-url'), {sensor_alert: state ? 1 : 0}).done(function (data) {
                    if (data.status === 'ok') {
                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                });
            });

        $('.remove-custom').on('click', function (event) {
            event.preventDefault();
            var $this = $(this);
            if ($this.hasClass('disabled')) return;

            wirelessSensorPost($this.data('update-url'), {sensor_custom: 'No'}).done(function (data) {
                if (data.status === 'ok') {
                    toastr.success(data.message);
                    $this.addClass('disabled');
                    clearLimits($this.data('sensor_id'));
                } else {
                    toastr.error(data.message);
                }
            });
        });
    </script>
@endpush
