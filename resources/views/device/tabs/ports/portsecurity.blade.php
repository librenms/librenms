<x-panel body-class="tw:p-0!">
    <x-slot name="title">
        @lang('Port Security')
    </x-slot>

    <table id="port-security-table" class="table table-hover table-condensed table-striped tw:mt-1 tw:mb-0!">
        <thead>
            <tr>
                <th data-column-id="device" data-visible="false">@lang('Device')</th>
                <th data-column-id="interface">@lang('Port')</th>
                <th data-column-id="port_description">@lang('Description')</th>
                <th data-column-id="enable">@lang('Enabled')</th>
                <th data-column-id="status" data-formatter="status">@lang('Status')</th>
                <th data-column-id="current_secure">@lang('Current MACs')</th>
                <th data-column-id="max_secure">@lang('Max MACs')</th>
                <th data-column-id="violation_action">@lang('Violation Action')</th>
                <th data-column-id="violation_count">@lang('Violations')</th>
                <th data-column-id="secure_last_mac">@lang('Last MAC')</th>
                <th data-column-id="sticky_enable">@lang('Sticky')</th>
            </tr>
        </thead>
    </table>
</x-panel>

<script>
    $(document).ready(function() {
        $("#port-security-table").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            post: function () {
                return {
                    device_id: {{ $device->device_id }},
                    searchby: "{{ request()->get('searchby') }}"
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
                        "<div class=\"col-sm-8 actionBar\"><span class=\"pull-left\">@lang('Ports') » @lang('Port Security')</span></div>" +
                        "<div class=\"col-sm-4 actionBar\"><p class=\"@{{css.search}}\"></p><p class=\"@{{css.actions}}\"></p></div></div></div>"
            }
        });
    });
</script>
