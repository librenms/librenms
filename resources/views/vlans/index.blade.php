@extends('layouts.librenmsv1')

@section('title', __('Vlans'))

@section('content')
    <div class="container-fluid">
        <x-panel body-class="!tw-p-0">
            <x-slot name="heading">
                <h2  class="panel-title">{{ __('VLAN') }}
                <select id="vlan-select">
                    @foreach($vlanIds as $vlanId)
                        <option>{{ $vlanId }}</option>
                    @endforeach
                </select>
                </h2>
            </x-slot>

            <table id="vlan-ports" class="table table-hover table-condensed table-striped">
                <thead>
                <tr>
                    <th data-column-id="device">{{ __('Device') }}</th>
                    <th data-column-id="port">{{ __('Port') }}</th>
                    <th data-column-id="untagged">{{ __('Untagged') }}</th>
                    <th data-column-id="state">{{ __('State') }}</th>
                    <th data-column-id="cost">{{ __('Cost') }}</th>
                </tr>
                </thead>
            </table>
        </x-panel>
    </div>
@endsection

@push('scripts')
    <script>
        var vlan_id = {{ (int) $vlanIds->first() }};
        var grid = $("#vlan-ports").bootgrid({
            ajax: true,
            // rowCount: [50, 100, 250, -1],
            post: function () {
                return {vlan: vlan_id}
            },
            url: "{{ route('table.vlan-ports') }}"
        });

        $('#vlan-select').on('change', function (event) {
            vlan_id = this.value;
            $("#vlan-ports").bootgrid('reload');
        })

    </script>
@endpush
