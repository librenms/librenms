@extends('layouts.librenmsv1')

@section('title', __('Alert History'))

@section('content')
    <div class="container-fluid">
        <template id="alertlog-filter-template"><x-filter name="alert-log" :fields="$filterFields" :initial="$filter" /></template>
        <x-panel title="{{ __('Alert History') }}">
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
    </div>

    @include('alerts.modals.details')
@endsection

@push('scripts')
<script>
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
                filter: filter
            };
        },
        converters: {
            datetime: {
                to: (value) => LibreNMS.Date.display(value) // app.js is a deferred module, resolve at render time
            }
        }
    }).on("loaded.rs.jquery.bootgrid", function () {
        grid.find(".incident-toggle").each(function () {
            $(this).parent().addClass('incident-toggle-td');
        }).on("click", function () {
            var target = $(this).data("target");
            $(target).collapse('toggle');
            $(this).toggleClass('fa-plus fa-minus');
        });
        grid.find(".verbose-alert-details").on("click", function (e) {
            e.preventDefault();
            $('#alert_log_id').val($(this).data('alert_log_id'));
            $("#alert_details_modal").modal('show');
        });
        grid.find(".incident").each(function () {
            $(this).parent().addClass('col-lg-4 col-md-4 col-sm-4 col-xs-4');
            if ($(this).parent().parent().find(".alert-status").hasClass('label-danger')) {
                $(this).parent().parent().find(".verbose-alert-details").fadeIn(0);
            }
            $(this).parent().parent().on("mouseenter", function () {
                $(this).find(".incident-toggle").fadeIn(200);
            }).on("mouseleave", function () {
                $(this).find(".incident-toggle").fadeOut(200);
            }).on("click", "td:not(.incident-toggle-td)", function () {
                var $toggle = $(this).parent().find(".incident-toggle");
                if ($toggle.hasClass('fa-plus')) {
                    $toggle.toggleClass('fa-plus fa-minus');
                    $($toggle.data("target")).collapse('toggle');
                }
            });
        });
    });

    const $template = $('#alertlog-filter-template');
    if ($template.length) {
        $(".alertlog-actionbar").prepend($($template[0].content.cloneNode(true)));
    }

    $(window).on('filter:apply', function (event) {
        if (event.originalEvent.detail.name === 'alert-log') {
            filter = event.originalEvent.detail.filters;
            grid.bootgrid('reload');
        }
    });
</script>
@endpush
