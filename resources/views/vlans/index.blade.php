@extends('layouts.librenmsv1')

@section('title', __('Vlans'))

@section('content')
    <div class="container-fluid">
        <x-panel body-class="!tw-p-0">
            <x-slot name="heading">
                <h2 class="panel-title">{{ __('VLAN') }}
                <select id="vlan-select">
                    @foreach($vlanIds as $vlanId)
                        <option>{{ $vlanId }}</option>
                    @endforeach
                </select>
                </h2>
            </x-slot>

            <x-tabs>
                <x-tab name="{{ __('Devices') }}">
                    <table id="vlan-devices" class="table table-hover table-condensed table-striped">
                        <thead>
                        <tr>
                            <th data-column-id="device">{{ __('Device') }}</th>
                            <th data-column-id="ports_count">{{ __('Ports') }}</th>
                            <th data-column-id="name">{{ __('Local Name') }}</th>
                            <th data-column-id="domain" data-visible="false">{{ __('Domain') }}</th>
                            <th data-column-id="type">{{ __('Type') }}</th>
                            <th data-column-id="mtu">{{ __('MTU') }}</th>
                        </tr>
                        </thead>
                    </table>
                </x-tab>
                <x-tab value="image" name="{{ __('Ports') }}">
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
                </x-tab>
            </x-tabs>
        </x-panel>
    </div>
@endsection

@push('scripts')
    <script>
        var vlan_id = {{ (int) $vlanIds->first() }};
        var grid = $("#vlan-ports").bootgrid({
            ajax: true,
            post: function () {
                return {vlan: vlan_id}
            },
            url: "{{ route('table.vlan-ports') }}"
        });
        var grid = $("#vlan-devices").bootgrid({
            ajax: true,
            post: function () {
                return {vlan: vlan_id}
            },
            url: "{{ route('table.vlan-devices') }}"
        });

        $('#vlan-select').on('change', function () {
            vlan_id = this.value;
            $("#vlan-ports").bootgrid('reload');
            $("#vlan-devices").bootgrid('reload');
        });
    </script>
@endpush
