<template id="alerts-filter-template"><x-filter :name="$filterName" :fields="$filterFields" :initial="$filter" /></template>
<x-panel :title="$title ?? null">
    <x-slot:table>
        <div class="table-responsive">
            <table id="alerts" class="table table-hover table-condensed alerts">
                <thead>
                <tr>
                    <th data-column-id="severity"></th>
                    <th data-column-id="timestamp">{{ __('Timestamp') }}</th>
                    <th data-column-id="rule">{{ __('Rule') }}</th>
                    <th data-column-id="details" data-sortable="false"></th>
                    <th data-column-id="hostname">{{ __('Hostname') }}</th>
                    <th data-column-id="location">{{ __('Location') }}</th>
                    <th data-column-id="ack_ico" data-sortable="false">{{ __('ACK') }}</th>
                    <th data-column-id="notes" data-sortable="false">{{ __('Notes') }}</th>
                    @can('alert.detail')
                        <th data-column-id="verbose_details" data-sortable="false">{{ __('Details') }}</th>
                    @endcan
                </tr>
                </thead>
            </table>
        </div>
    </x-slot:table>
</x-panel>

@include('alerts.modals.details')
@include('alerts.modals.notes')
@include('alerts.modals.ack')

@push('scripts')
<script>
    (function () {
        var filter = @js($filter);

        var grid = $("#alerts").bootgrid({
            ajax: true,
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"alerts-actionbar tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2\"><p class=\"@{{css.actions}}\"></p></div></div>",
                search: "" // replaced by the filter bar
            },
            post: function () {
                return {
                    filter: filter,
                    @isset($alertId)
                    alert_id: @js($alertId),
                    @endisset
                    @isset($deviceId)
                    device_id: @js($deviceId),
                    @endisset
                };
            },
            url: "{{ route('table.alerts') }}",
            sort: {
                timestamp: 'desc',
            },
            rowCount: [50, 100, 250, -1],
        }).on("loaded.rs.jquery.bootgrid", function () {
            @include('alerts.partials.init-rows', ['grid' => 'grid'])
        });

        const $template = $('#alerts-filter-template');
        if ($template.length) {
            $(".alerts-actionbar").prepend($($template[0].content.cloneNode(true)));
        }

        $(window).on('filter:apply', function (event) {
            if (event.originalEvent.detail.name === @js($filterName)) {
                filter = event.originalEvent.detail.filters;
                grid.bootgrid('reload');
            }
        });
    })();
</script>
@endpush
