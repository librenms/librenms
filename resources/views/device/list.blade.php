<template id="device-filter-template"><x-filter name="devices" :fields="$filterFields" :initial="$filter" :hide="$hideFilter" /></template>

<div x-data="{ group: @js($group) }" x-init="window.addEventListener('filter:apply', (e) => $data.group = e.detail.filters['groups.id']?.eq);">
    <h3 x-show="group" x-cloak class="tw:px-4">
        <span class="devices-font-bold">{{ __('Device Group') }}: </span>
        <span x-text="group"></span>
    </h3>
    <div class="table-responsive">
        <table id="devices" class="table table-hover table-condensed table-striped"
               data-url="{{ route('table.device') }}">
            <thead>
            <tr>
                <th data-column-id="status" data-formatter="status" data-width="7px" data-searchable="false">{{ $detailed ? 'S.' : __('Status') }}</th>
                <th data-column-id="device_id" data-width="5px" data-visible="{{ $detailed ? 'true' : 'false' }}">{{ __('Id') }}</th>
                <th data-column-id="maintenance" data-width="5px" data-searchable="false" data-formatter="maintenance" data-visible="{{ $detailed ? 'true' : 'false' }}">{{ $detailed ? 'M.' : __('Maintenance') }}</th>
                <th data-column-id="icon" data-width="70px" data-searchable="false" data-formatter="icon" data-visible="{{ $detailed ? 'true' : 'false' }}">{{ __('Vendor') }}</th>
                <th data-column-id="hostname" data-order="asc" {!! $detailed ? 'data-formatter="device"' : '' !!}>{{ __('Device') }}</th>
                <th data-column-id="metrics" data-width="{{ $detailed ? '100px' : '150px' }}" data-sortable="false" data-searchable="false" data-visible="{{ $detailed ? 'true' : 'false' }}">{{ __('Metrics') }}</th>
                <th data-column-id="hardware">{{ __('Platform') }}</th>
                <th data-column-id="os">{{ __('device.attributes.os') }}</th>
                <th data-column-id="uptime" data-formatter="uptime">{{ __('Up/Down Time') }}</th>
                <th data-column-id="location" data-visible="{{ $detailed ? 'true' : 'false' }}">{{ __('device.attributes.location') }}</th>
                <th data-column-id="actions" data-width="{{ $detailed ? '120px' : '240px' }}" data-sortable="false" data-searchable="false" data-header-css-class="device-table-header-actions">{{ __('Actions') }}</th>
            </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
        <script>
            var filter = @js($filter);

            var grid = $("#devices").bootgrid({
                ajax: true,
                rowCount: [50, 100, 250, -1],
                columnSelection: true,
                formatters: {
                    "status": function (column, row) {
                        return "<span title=\"Status: " + row.status + " : " + row.extra.replace(/^label-/,'') + "\" class=\"{{ $detailed ? 'alert-status' : 'alert-status-small' }} " + row.extra + "\"></span>";
                    },
                    "icon": function (column, row) {
                        return "<span class=\"device-table-icon tw:dark:bg-gray-50 tw:dark:rounded-lg tw:dark:p-2\">" + row.icon + "</span>";
                    },
                    "maintenance": function (column, row) {
                        if (row.maintenance) {
                            return "<span title=\"Scheduled Maintenance\" class=\"glyphicon glyphicon-wrench\"></span>";
                        }
                        return '';
                    },
                    "device": function (column, row) {
                        return "<span>" + row.hostname + "</span>";
                    },
                    "uptime": function (column, row) {
                        if (row.status == 'down') {
                            return "<span class='red'>" + row.uptime + "</span>"
                        } else if(row.status == 'disabled') {
                            return '';
                        } else {
                            return row.uptime;
                        }
                    },
                },
                templates: {
                    header: "<div class=\"devices-headers-table-menu\" style=\"padding:6px 6px 0px 0px;\"><p class=\"@{{css.actions}}\"></p></div><div class=\"row\"></div>",
                    search: "" // hide the generic search
                },
                post: function () {
                    return {
                        format: '{{ $detailed ? 'list_detail' : 'list_basic' }}',
                        filter: filter
                    };
                },
            });

            const $template = $('#device-filter-template');
            if ($template.length) {
                const $content = $($template[0].content.cloneNode(true));
                const $wrapper = $('<div class="pull-left"></div>');
                $wrapper.append($content);
                $(".devices-headers-table-menu").append($wrapper);
            }

            $(window).on('filter:apply', function (event) {
                if (event.originalEvent.detail.name === 'devices') {
                    filter = event.originalEvent.detail.filters;
                    grid.bootgrid('reload');
                }
            });
        </script>
    @endpush
</div>
