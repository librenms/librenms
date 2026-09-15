@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-panel>
            <div class="table-responsive">
                <table id="vminfo" class="table table-hover table-condensed table-striped"
                       data-url="{{ route('table.vminfo') }}"
                       data-params="device_id={{ $device->device_id }}">
                    <thead>
                        <tr>
                            <th data-column-id="vmwVmDisplayName" data-order="asc">{{ __('VM Name') }}</th>
                            <th data-column-id="vmwVmState">{{ __('Power Status') }}</th>
                            <th data-column-id="vm_type">{{ __('Type') }}</th>
                            <th data-column-id="vmwVmGuestOS" data-searchable="false">{{ __('Operating System') }}</th>
                            <th data-column-id="vmwVmMemSize" data-searchable="false">{{ __('Memory') }}</th>
                            <th data-column-id="vmwVmCpus" data-searchable="false">{{ __('vCPUs') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </x-panel>
    </x-device.page>
@endsection

@section('scripts')
<script>
    $("#vminfo").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function () {
            return {
                device_id: {{ $device->device_id }},
            }
        }
    })
</script>
@endsection
