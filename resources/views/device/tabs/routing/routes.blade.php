@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('Routing Table')">
        <x-device.routing-tabs :device="$device" tab="routes" />

        <x-panel>
            <div class="table-responsive">
                <table id="routes" class="table table-condensed table-hover table-striped">
                    <thead>
                        <tr>
                            <th data-column-id="context_name" data-width="125px">{{ __('VRF') }}</th>
                            <th data-column-id="inetCidrRouteDestType" data-width="70px">{{ __('Proto') }}</th>
                            <th data-column-id="inetCidrRouteDest">{{ __('Destination') }}</th>
                            <th data-column-id="inetCidrRoutePfxLen" data-width="80px">{{ __('Mask') }}</th>
                            <th data-column-id="inetCidrRouteNextHop">{{ __('Next hop') }}</th>
                            <th data-column-id="inetCidrRouteIfIndex">{{ __('Interface') }}</th>
                            <th data-column-id="inetCidrRouteMetric1" data-width="85px">{{ __('Metric') }}</th>
                            <th data-column-id="inetCidrRouteType" data-width="85px">{{ __('Type') }}</th>
                            <th data-column-id="inetCidrRouteProto" data-width="85px">{{ __('Proto') }}</th>
                            <th data-column-id="created_at" data-width="165px">{{ __('First seen') }}</th>
                            <th data-column-id="updated_at" data-width="165px">{{ __('Last seen') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="alert alert-info tw:mt-4">
                {{ __('Warning: Routing Table is only retrieved during device discovery. Devices are skipped if they have more than :max routes.', ['max' => $max_routes]) }}
            </div>
        </x-panel>
    </x-device.page>
@endsection

@section('scripts')
<script>
$(function () {
    var grid = $("#routes").bootgrid({
        ajax: true,
        post: function () {
            var check_showAllRoutes = document.getElementById('check_showAllRoutes');
            var showAllRoutes = check_showAllRoutes ? check_showAllRoutes.checked : false;

            var list_showProtocols = document.getElementById('list_showProtocols');
            var showProtocols = list_showProtocols ? list_showProtocols.value : 'all';

            return {
                device_id: {{ $device->device_id }},
                showAllRoutes: showAllRoutes,
                showProtocols: showProtocols
            };
        },
        url: "{{ url('ajax/table/routes') }}"
    });

    $(".actionBar").append(
        '<div class="search form-group pull-left tw:w-auto">' +
        '@csrf' +
        '<select name="list_showProtocols" id="list_showProtocols" class="input-sm" onChange="updateTable();">' +
        '<option value="all">{{ __("all Protocols") }}</option>' +
        '<option value="ipv4">{{ __("IPv4 only") }}</option>' +
        '<option value="ipv6">{{ __("IPv6 only") }}</option>' +
        '</select>&nbsp;' +
        '<label class="tw:font-normal tw:ml-1"><input type="checkbox" name="check_showAllRoutes" id="check_showAllRoutes">' +
        '&nbsp;{{ __("Include historical routes in the table") }}</label>' +
        '</div>'
    );

    $(document).on('change', '#check_showAllRoutes', function() {
        updateTable();
    });
});

function updateTable() {
    $('#routes').bootgrid('reload');
}
</script>
@endsection
