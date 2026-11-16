<template id="alertlog-filter-template"><x-filter :name="$filterName" :fields="$filterFields" :initial="$filter" /></template>
<x-panel :title="$title ?? null">
    <x-slot:table>
        <div class="table-responsive">
            <table id="alertlog" class="table table-hover table-condensed table-striped" data-url="{{ route('table.alertlog') }}">
                <thead>
                <tr>
                    <th data-column-id="status">{{ __('State') }}</th>
                    <th data-column-id="time_logged" data-order="desc" data-converter="datetime">{{ __('Timestamp') }}</th>
                    <th data-column-id="details" data-sortable="false">&nbsp;</th>
                    <th data-column-id="hostname">{{ __('Device') }}</th>
                    <th data-column-id="alert_rule">{{ __('Alert') }}</th>
                    <th data-column-id="severity">{{ __('Severity') }}</th>
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

@push('scripts')
<script>
    (function () {
        var filter = @js($filter);

        var grid = $("#alertlog").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"alertlog-actionbar tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2\"><p class=\"@{{css.actions}}\"></p></div></div>",
                search: "" // replaced by the filter bar
            },
            post: function () {
                return {
                    filter: filter,
                    @isset($deviceId)
                    device_id: @js($deviceId),
                    @endisset
                };
            },
            converters: {
                datetime: {
                    to: (value) => LibreNMS.Date.display(value) // app.js is a deferred module, resolve at render time
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function () {
            @include('alerts.partials.init-rows', ['grid' => 'grid', 'expandOnRowClick' => true])
        });

        const $template = $('#alertlog-filter-template');
        if ($template.length) {
            $(".alertlog-actionbar").prepend($($template[0].content.cloneNode(true)));
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
