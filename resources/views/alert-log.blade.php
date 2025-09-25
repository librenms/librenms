@extends('layouts.librenmsv1')

@section('title', __('Alert Log'))

@section('content')
    <div class="container-fluid">
        <x-panel body-class="tw:p-0!">
            <x-slot name="heading">
                <h3 class="panel-title">@lang('Alert Log entries')</h3>
            </x-slot>

            <!-- Filter Form -->
            <table id="alertlog" class="table table-hover table-condensed table-striped tw:w-full"
                   data-url="{{ route('table.alertlog') }}">
                <thead>
                <tr>
                    <th data-column-id="status" data-sortable="false">@lang('State')</th>
                    <th data-column-id="time_logged" data-order="desc">@lang('Timestamp')</th>
                    <th data-column-id="details" data-sortable="false" class="tw:hidden sm:tw:table-cell">&nbsp;</th>
                    <th data-column-id="hostname">@lang('Device')</th>
                    <th data-column-id="alert">@lang('Alert')</th>
                    <th data-column-id="severity" class="tw:hidden md:tw:table-cell">@lang('Severity')</th>
                    @if(auth()->user()->hasGlobalAdmin())
                        <th data-column-id="verbose_details" data-sortable="false" class="tw:hidden lg:tw:table-cell">@lang('Details')</th>
                    @endif
                </tr>
                </thead>
            </table>
        </x-panel>
    </div>

    <!-- Alert details modal -->
    <div class="modal fade" id="alert_details_modal" tabindex="-1" role="dialog" aria-labelledby="alert_details_modal_label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="alert_details_modal_label">@lang('Alert Details')</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <textarea class="form-control" id="alert_details_content" rows="20" readonly>{{ __('Loading...') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function validateDateRange() {
            var dateFrom = $("#date_from").val();
            var dateTo = $("#date_to").val();
            var $dateFromInput = $("#date_from");
            var $dateToInput = $("#date_to");
            var $dateMessage = $("#date-validation-message");

            $dateMessage.remove();

            if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
                $dateToInput.after('<div id="date-validation-message" class="alert alert-warning tw:mt-2" role="alert">' +
                    '<i class="fa fa-exclamation-triangle"></i> ' +
                    '@lang("From date must be before or equal to To date.")' +
                    '</div>');
                return false;
            }

            return true;
        }

        var grid = $("#alertlog").bootgrid({
            ajax: true,
            searchable: true,
            navigation: 3,
            rowCount: [25, 50, 100, 250, 500, -1],
            templates: {
                header:
                    "<div class=\"alertlog-headers-table-menu\" style=\"padding:6px 6px 0px 0px;\">" +
                    "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}} tw:flex tw:flex-wrap\">" +
                        "<form method=\"post\" action=\"{{ route('alert-log') }}\" class=\"tw:flex tw:flex-wrap tw:items-center\" role=\"form\" id=\"alertlog_filter\">" +
                        "{!! addslashes(csrf_field()) !!}" +
                        "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                        "<span class=\"tw:mr-1\">@lang('Device')</span>" +
                        "<select name=\"device_id\" id=\"device_id\" class=\"form-control\"></select>" +
                        "</div>" +
                        "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                        "<span class=\"tw:mr-1\">@lang('Alert Rule')</span>" +
                        "<select name=\"rule_id\" id=\"rule_id\" class=\"form-control\"></select>" +
                        "</div>" +
                        "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                        "<span class=\"tw:mr-1\">@lang('Device Group')</span>" +
                        "<select name=\"device_group\" id=\"device_group\" class=\"form-control\"></select>" +
                        "</div>" +
                        "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                        "<span class=\"tw:mr-1\">@lang('State')</span>" +
                        "<select name=\"state\" id=\"state\" class=\"form-control\">" +
                        "@foreach($alert_states as $name => $value)<option value='{{ $value }}' {{ $filter['state'] == $value ? 'selected' : '' }}>{{ $name }}</option>@endforeach" +
                        "</select>" +
                        "</div>" +
                        "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                        "<span class=\"tw:mr-1\">@lang('Severity')</span>" +
                        "<select name=\"min_severity\" id=\"min_severity\" class=\"form-control\">" +
                        "@foreach($alert_severities as $name => $value)<option value='{{ $value }}' {{ $filter['min_severity'] == $value ? 'selected' : '' }}>{{ $name }}</option>@endforeach" +
                        "</select>" +
                        "</div>" +
                        "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                        "<span class=\"tw:mr-1\">@lang('From Date')</span>" +
                        "<input type=\"date\" name=\"date_from\" id=\"date_from\" class=\"form-control\" value=\"{{ $filter['date_from'] ?? '' }}\" style=\"width: 150px;\">" +
                        "</div>" +
                        "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                        "<span class=\"tw:mr-1\">@lang('To Date')</span>" +
                        "<input type=\"date\" name=\"date_to\" id=\"date_to\" class=\"form-control\" value=\"{{ $filter['date_to'] ?? '' }}\" style=\"width: 150px;\">" +
                        "</div>" +
                        "<button type=\"submit\" class=\"btn btn-default tw:mr-2 tw:mt-2\">@lang('Filter')</button>" +
                        "</form>" +
                    "<div class=\"actionBar tw:ml-auto tw:relative tw:mt-2\"><div class=\"@{{css.actions}}\"></div></div>" +
                    "</div>" +
                    "</div>"
            },
            requestHandler: function (request) {
                // Prevent loading all entries without a device or device group selected
                if (request.rowCount === -1) {
                    var deviceSelected = $("#device_id").val();
                    var deviceGroupSelected = $("#device_group").val();

                    if ((!deviceSelected || deviceSelected === "") && (!deviceGroupSelected || deviceGroupSelected === "")) {
                        var $alertlogContainer = $("#alertlog").closest('.panel');
                        var $alertMessage = $("#alert-validation-message");

                        $alertMessage.remove();

                        $alertlogContainer.before('<div id="alert-validation-message" class="alert alert-warning tw:mt-2" role="alert">' +
                            '<i class="fa fa-exclamation-triangle"></i> ' +
                            '@lang("alerting.alert_log.device_group_required")' +
                            '</div>');

                        return null;
                    }
                }

                return request;
            },
            post: function () {
                return {!! json_encode($filter) !!};
            },
        }).on("loaded.rs.jquery.bootgrid", function () {
            grid.find(".incident-toggle").each(function () {
                $(this).parent().addClass('incident-toggle-td');
            }).on("click", function (e) {
                var target = $(this).data("target");
                $(target).collapse('toggle');
                $(this).toggleClass('fa-plus fa-minus');
            });

            grid.find(".verbose-alert-details").on("click", function(e) {
                e.preventDefault();
                var alert_log_id = $(this).data('alert_log_id');

                // Reset modal content
                $('#alert_details_content').val('{{ __('Loading...') }}');
                $("#alert_details_modal").modal('show');

                // Load alert details via AJAX
                $.ajax({
                    type: "GET",
                    url: "{{ url('ajax/table/alertlog') }}/" + alert_log_id + "/details",
                    dataType: "json",
                    success: function (data) {
                        if (data.status === 'ok') {
                            $("#alert_details_content").val(JSON.stringify(data.details, null, 2));
                        } else {
                            $("#alert_details_content").val('Error: ' + data.message);
                        }
                    },
                    error: function() {
                        $("#alert_details_content").val('Error loading alert details');
                    }
                });
            });

            // Style incident rows
            grid.find(".incident").each(function () {
                $(this).parent().addClass('col-lg-4 col-md-4 col-sm-4 col-xs-4');
                if ($(this).parent().parent().find(".alert-status").hasClass('label-danger')){
                }
                $(this).parent().parent().on("mouseenter", function () {
                    $(this).find(".incident-toggle").fadeIn(200);
                    if ($(this).find(".alert-status").hasClass('label-danger')){
                    }
                }).on("mouseleave", function () {
                    $(this).find(".incident-toggle").fadeOut(200);
                    if ($(this).find(".alert-status").hasClass('label-danger')){
                    }
                }).on("click", "td:not(.incident-toggle-td)", function () {
                    var target = $(this).parent().find(".incident-toggle").data("target");
                    if ($(this).parent().find(".incident-toggle").hasClass('fa-plus')) {
                        $(this).parent().find(".incident-toggle").toggleClass('fa-plus fa-minus');
                        $(target).collapse('toggle');
                    }
                });
            });
        });

        // Initialize device selector
        init_select2("#device_id", "device", {}, @json($device_selected), "@lang('All Devices')");

        // Initialize alert rule selector
        init_select2("#rule_id", "alert-rules", {}, @json($rule_selected ?? ''), "@lang('All Alert Rules')");

        // Initialize device group selector
        init_select2("#device_group", "device-group", {}, @json($device_group_selected ?? ''), "@lang('All Device Groups')");

        // Clear validation message when device or device group changes
        $("#device_id, #device_group").on("change", function() {
            $("#alert-validation-message").remove();
        });

        // Validate date range on change and form submit
        $("#date_from, #date_to").on("change", function() {
            validateDateRange();
        });
        $("#alertlog_filter").on("submit", function(e) {
            if (!validateDateRange()) {
                e.preventDefault();
                return false;
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .actionBar > .actions {
            display: flex;
        }
        .actionBar > .actions > * {
            float: none;
        }

        #alert_details_content {
            background-color: white !important;
            color: #333 !important;
        }

        #alertlog {
            table-layout: auto;
        }

        @media (max-width: 640px) {
            #alertlog th:nth-child(3),
            #alertlog td:nth-child(3) {
                display: none !important;
            }

            #alertlog td:nth-child(2) {
                white-space: normal !important;
                max-width: 96px;
                font-size: 12px;
                line-height: 1.2;
                padding: 4px 2px !important;
            }

            #alertlog td:nth-child(4) {
                max-width: 100px;
                font-size: 13px;
            }

            #alertlog td:nth-child(5) {
                min-width: 120px;
                white-space: normal !important;
                line-height: 1.3;
                padding: 8px 4px !important;
                font-size: 13px;
            }
        }

        @media (max-width: 767px) {
            #alertlog th:nth-child(7),
            #alertlog td:nth-child(7) {
                display: none !important;
            }
        }
    </style>
@endpush
