@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-file-info-text-o" aria-hidden="true"></i> {{ __('Coriant NE Hardware') }}</h3>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="tnmsne" class="table table-hover table-condensed tnmsne">
                    <thead>
                        <tr>
                            <th data-column-id="neName">{{ __('Name') }}</th>
                            <th data-column-id="neLocation">{{ __('Location') }}</th>
                            <th data-column-id="neType">{{ __('Type') }}</th>
                            <th data-column-id="neOpMode">{{ __('Operation Mode') }}</th>
                            <th data-column-id="neAlarm">{{ __('Alarm') }}</th>
                            <th data-column-id="neOpState">{{ __('State') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            $("#tnmsne").bootgrid({
                ajax: true,
                rowCount: [50, 100, 250, -1],
                post: function() {
                    return {
                        device_id: {{ $device->device_id }},
                    };
                },
                url: "{{ route('table.tnmsne') }}",
                formatters: {},
                templates: {}
            });
        });
    </script>
</x-device.page>
@endsection
