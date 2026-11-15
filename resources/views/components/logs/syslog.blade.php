@props([
    'device' => null,
    'filterName',
    'filter' => [],
    'filterFields' => [],
    'from' => '',
    'to' => '',
])

<x-panel title="{{ __('Syslog') }}">
    <x-slot:table>
        <div class="table-responsive">
            <table id="syslog" class="table table-hover table-condensed table-striped" data-url="{{ route('table.syslog') }}">
                <thead>
                <tr>
                    <th data-column-id="label"></th>
                    <th data-column-id="timestamp" data-order="desc">{{ __('Timestamp') }}</th>
                    <th data-column-id="level">{{ __('Level') }}</th>
                    <th data-column-id="device_id">{{ __('device.attributes.hostname') }}</th>
                    <th data-column-id="program">{{ __('Program') }}</th>
                    <th data-column-id="msg">{{ __('Message') }}</th>
                    <th data-column-id="priority">{{ __('Priority') }}</th>
                </tr>
                </thead>
            </table>
        </div>
    </x-slot:table>
</x-panel>

<template id="syslog-filter">
    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
        <x-filter :name="$filterName" :fields="$filterFields" :initial="$filter" />
        <x-date-range-picker :start="$from" :end="$to" :reload="true" class="form-control tw:min-w-64" />
    </div>
</template>

@push('scripts')
<script>
    (function () {
        let filter = @js($filter);

        const grid = $("#syslog").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"syslog-actionbar tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2\"><p class=\"@{{css.actions}}\"></p></div></div>",
                search: "" // replaced by the filter bar
            },
            post: function () {
                return {
                    device: @js($device?->device_id),
                    from: @js($from),
                    to: @js($to),
                    filter: filter,
                };
            }
        });

        $('.syslog-actionbar').prepend(document.getElementById('syslog-filter').content.cloneNode(true));

        $(window).on('filter:apply', function (event) {
            if (event.originalEvent.detail.name === @js($filterName)) {
                filter = event.originalEvent.detail.filters;
                grid.bootgrid('reload');
            }
        });
    })();
</script>
@endpush
