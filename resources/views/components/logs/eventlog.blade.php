@props([
    'device' => null,
    'filterName',
    'filter' => [],
    'filterFields' => [],
])

<x-panel title="{{ __('Eventlog') }}">
    <x-slot:table>
        <div class="table-responsive">
            <table id="eventlog" class="table table-hover table-condensed table-striped" data-url="{{ route('table.eventlog') }}">
                <thead>
                <tr>
                    <th data-column-id="datetime" data-order="desc">{{ __('Timestamp') }}</th>
                    <th data-column-id="type">{{ __('Type') }}</th>
                    <th data-column-id="device_id">{{ __('device.attributes.hostname') }}</th>
                    <th data-column-id="message">{{ __('Message') }}</th>
                    <th data-column-id="username">{{ __('User') }}</th>
                </tr>
                </thead>
            </table>
        </div>
    </x-slot:table>
</x-panel>

<template id="eventlog-filter">
    <x-filter :name="$filterName" :fields="$filterFields" :initial="$filter" />
</template>

@push('scripts')
<script>
    (function () {
        let filter = @js($filter);

        const grid = $("#eventlog").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"eventlog-actionbar tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2\"><p class=\"@{{css.actions}}\"></p></div></div>",
                search: "" // replaced by the filter bar
            },
            post: function () {
                return {
                    device: @js($device?->device_id),
                    filter: filter,
                };
            }
        });

        $('.eventlog-actionbar').prepend(document.getElementById('eventlog-filter').content.cloneNode(true));

        $(window).on('filter:apply', function (event) {
            const { name, filters, initial } = event.originalEvent.detail;
            if (!initial && name === @js($filterName)) {
                filter = filters;
                grid.bootgrid('reload');
            }
        });
    })();
</script>
@endpush
