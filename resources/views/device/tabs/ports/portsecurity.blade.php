<x-panel>
    <x-slot:slot class="tw:p-0!">
    <table id="port-security-table" class="table table-hover table-condensed table-striped tw:mt-1 tw:mb-0!">
        <thead>
            <tr>
                <th data-column-id="device" data-visible="false">{{ __('Device') }}</th>
                <th data-column-id="interface">{{ __('Port') }}</th>
                <th data-column-id="port_description">{{ __('Description') }}</th>
                <th data-column-id="enable">{{ __('Enabled') }}</th>
                <th data-column-id="status" data-formatter="status">{{ __('Status') }}</th>
                <th data-column-id="current_secure">{{ __('Current MACs') }}</th>
                <th data-column-id="max_secure">{{ __('Max MACs') }}</th>
                <th data-column-id="violation_action">{{ __('Violation Action') }}</th>
                <th data-column-id="violation_count">{{ __('Violations') }}</th>
                <th data-column-id="secure_last_mac">{{ __('Last MAC') }}</th>
                <th data-column-id="sticky_enable">{{ __('Sticky') }}</th>
            </tr>
        </thead>
    </table>
    </x-slot:slot>
</x-panel>

<script>
    $(document).ready(function() {
        $("#port-security-table").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            post: function () {
                return {
                    device_id: {{ $device->device_id }},
                    searchby: "{{ request()->input('searchby') }}"
                };
            },
            url: "{{ url('/ajax/table/port-security') }}",
            formatters: {
                "status": function (column, row) {
                    return row.status;
                }
            },
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"row\">" +
                        "<div class=\"col-sm-8 actionBar\"><span class=\"pull-left\"></span></div>" +
                        "<div class=\"col-sm-4 actionBar\"><p class=\"@{{css.search}}\"></p><p class=\"@{{css.actions}}\"></p></div></div></div>"
            }
        });
    });
</script>
