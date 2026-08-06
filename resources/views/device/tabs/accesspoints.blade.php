@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        @if($data['accessPoint'])
            <p>
                <a href="{{ route('device', ['device' => $device, 'tab' => 'accesspoints']) }}">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    {{ __('All access points') }}
                </a>
            </p>

            <x-panel title="{{ __('Access Point') }}">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped">
                        <tbody>
                        <tr>
                            <th scope="row">{{ __('Name') }}</th>
                            <td>{{ $data['accessPoint']->name }}</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('MAC Address') }}</th>
                            <td>{{ $data['accessPoint']->mac_addr }}</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('Radio') }}</th>
                            <td>{{ $data['accessPoint']->type }} ({{ $data['accessPoint']->radio_number }})</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('Channel') }}</th>
                            <td>{{ $data['accessPoint']->channel }}</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('Clients') }}</th>
                            <td>{{ $data['accessPoint']->numasoclients }}</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('Utilization') }}</th>
                            <td>{{ $data['accessPoint']->radioutil }}%</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('Interference Index') }}</th>
                            <td>{{ $data['accessPoint']->interference }}</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('Transmit Power') }}</th>
                            <td>{{ $data['accessPoint']->txpow }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </x-panel>

            @foreach($data['graphs'] as $graph)
                <x-graph-row
                    loading="lazy"
                    columns="responsive"
                    :type="$graph['type']"
                    :title="$graph['title']"
                    :vars="['id' => $data['accessPoint']->accesspoint_id]"
                ></x-graph-row>
            @endforeach
        @else
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
        @endif
    </x-device.page>
@endsection

@if(! $data['accessPoint'])
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
@endif
