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
            grid.find(".incident-toggle").each(function () {
                $(this).parent().addClass('incident-toggle-td');
            }).on("click", function () {
                var target = $(this).data("target");
                $(target).collapse('toggle');
                $(this).toggleClass('fa-plus fa-minus');
            });
            grid.find(".incident").each(function () {
                $(this).parent().addClass('col-lg-4 col-md-4 col-sm-4 col-xs-4');
                $(this).parent().parent().on("mouseenter", function () {
                    $(this).find(".incident-toggle").fadeIn(200);
                }).on("mouseleave", function () {
                    $(this).find(".incident-toggle").fadeOut(200);
                });
            });
            grid.find(".command-ack-alert").on("click", function (e) {
                e.preventDefault();
                $('#ack_alert_id').val($(this).data('alert_id'));
                $('#ack_alert_state').val($(this).data("alert_state"));
                $('#ack_msg').val('');
                $("#alert_ack_modal").modal('show');
            });
            grid.find(".command-alert-note").on("click", function (e) {
                e.preventDefault();
                $('#alert_id').val($(this).data('alert_id'));
                $("#alert_notes_modal").modal('show');
            });
            grid.find(".command-alert-details").on("click", function (e) {
                e.preventDefault();
                $('#alert_log_id').val($(this).data('alert_log_id'));
                $("#alert_details_modal").modal('show');
            });
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
