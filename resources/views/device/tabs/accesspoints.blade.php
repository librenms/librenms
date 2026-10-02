@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-panel title="{{ __('Access Points') }}" id="access-points-panel">
            <div class="table-responsive">
                <table id="access-points" class="table table-condensed table-hover table-striped">
                    <thead>
                    <tr>
                        <th data-column-id="name" data-order="asc">{{ __('Access Point') }}</th>
                        <th data-column-id="radio">{{ __('Radio') }}</th>
                        <th data-column-id="channel">{{ __('Channel') }}</th>
                        <th data-column-id="numasoclients">{{ __('Clients') }}</th>
                        <th data-column-id="radioutil" data-formatter="percentage">{{ __('Utilization') }}</th>
                        <th data-column-id="interference">{{ __('Interference Index') }}</th>
                        <th data-column-id="txpow">{{ __('Transmit Power') }}</th>
                        <th data-column-id="trends" data-sortable="false" data-searchable="false">{{ __('Trends') }}</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </x-panel>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#access-points').bootgrid({
                ajax: true,
                rowCount: [50, 100, 250],
                url: "{{ route('table.access-points') }}",
                post: function () {
                    return {
                        device_id: '{{ $device->device_id }}'
                    };
                },
                formatters: {
                    percentage: function (column, row) {
                        return row[column.id] + '%';
                    }
                }
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        #access-points-panel > .panel-body {
            padding: 0;
        }

        #access-points {
            margin-bottom: 0;
        }
    </style>
@endpush
