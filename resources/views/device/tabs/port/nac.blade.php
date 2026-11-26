<x-panel>
    <x-slot:table>
        <table id="nac-grid" class="table table-hover table-condensed table-striped" data-url="{{ route('table.port-nac') }}">
            <thead>
            <tr>
                <th data-column-id="port_id" data-width="100px">{{ __('Ports') }}</th>
                <th data-column-id="mac_address" data-formatter="tooltip" data-width="170px">{{ __('Mac Address') }}</th>
                <th data-column-id="mac_oui" data-formatter="tooltip" data-sortable="false" data-width="130px" @notconfig('mac_oui.enabled')data-visible="false"@endnotconfig>{{ __('Vendor') }}</th>
                <th data-column-id="ip_address" data-formatter="tooltip" data-width="140px">{{ __('IP Address') }}</th>
                <th data-column-id="vlan" data-formatter="tooltip" data-width="60px">{{ __('Vlan') }}</th>
                <th data-column-id="domain" data-formatter="nac_domain">{{ __('Domain') }}</th>
                <th data-column-id="host_mode" data-formatter="tooltip">{{ __('Host Mode') }}</th>
                <th data-column-id="username" data-formatter="tooltip" data-width="250px">{{ __('Username') }}</th>
                <th data-column-id="authz_by" data-formatter="tooltip">{{ __('Auth By') }}</th>
                <th data-column-id="timeout" data-formatter="time_interval">{{ __('Timeout') }}</th>
                <th data-column-id="time_elapsed" data-formatter="time_interval">{{ __('Time Elapsed') }}</th>
                <th data-column-id="time_left" data-formatter="time_interval" data-visible="false">{{ __('Time Left') }}</th>
                <th data-column-id="authc_status" data-formatter="nac_authc">{{ __('NAC Authc') }}</th>
                <th data-column-id="authz_status" data-formatter="nac_authz">{{ __('NAC Authz') }}</th>
                <th data-column-id="method" data-formatter="nac_method">{{ __('NAC Method') }}</th>
                <th data-column-id="created_at" data-formatter="tooltip">{{ __('First seen') }}</th>
                <th data-column-id="updated_at" data-formatter="tooltip">{{ __('Last seen') }}</th>
            </tr>
            </thead>
        </table>
    </x-slot:table>
</x-panel>

<template id="nac-grid-filter">
    <div class="form-group pull-left tw:mr-2">
        <label class="checkbox-inline"><input type="checkbox" id="nac-historical" checked> {{ __('Include historical NAC entries') }}</label>
    </div>
</template>

@push('scripts')
<script>
    (function () {
        const icon = (name, color) => `<i class="fa fa-${name} fa-lg icon-theme" aria-hidden="true"${color ? ` style="color:${color};"` : ''}></i>`;
        const label = (value, type = 'default') => $('<span>').addClass(`label label-${type}`).attr('title', value).text(value).prop('outerHTML');
        const success = icon('check-circle', 'green');
        const failure = icon('times-circle', 'red');

        $("#nac-grid").bootgrid({
            ajax: true,
            rowCount: [25, 50, 100, -1],
            post: () => ({
                port_id: @js($port->port_id),
                showHistorical: $('#nac-historical').length ? $('#nac-historical').is(':checked') : true,
            }),
            formatters: {
                time_interval: (column, row) => {
                    const duration = humanize_duration(row[column.id]);
                    return $('<span>').attr('title', duration.trim()).text(duration.split(' ').slice(0, 2).join(' ')).prop('outerHTML');
                },
                tooltip: (column, row) => {
                    const value = row[column.id];
                    const title = column.id === 'mac_address' && row.mac_oui ? `${value} (${row.mac_oui})` : value;
                    return $('<span>').attr('title', title).text(value).prop('outerHTML');
                },
                // huawei MIB has the typo sussess
                nac_authz: (column, row) => ({authorizationSuccess: success, sussess: success, authorizationFailed: failure})[row[column.id]] ?? label(row[column.id]),
                nac_domain: (column, row) => ({voice: icon('phone'), data: icon('desktop'), Disabled: icon('desktop')})[row[column.id]] ?? label(row[column.id]),
                nac_authc: (column, row) => ({notRun: label('notRun', 'primary'), running: label('running', 'primary'), failedOver: failure, authcSuccess: success, authcFailed: failure, 6: failure})[row[column.id]] ?? label(row[column.id]),
                nac_method: (column, row) => ({dot1x: label('802.1x', 'success'), macAuthBypass: label('MAB', 'primary'), other: label(@js(__('Disabled')), 'danger')})[row[column.id]] ?? label(row[column.id]),
            },
        });

        $('#nac-grid-header .actionBar').prepend($('#nac-grid-filter').html());
        $('#nac-historical').on('change', () => $('#nac-grid').bootgrid('reload'));
    })();
</script>
@endpush
