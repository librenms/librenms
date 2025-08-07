@extends('layouts.librenmsv1')

@section('title', __('Outages'))

@php
// Default refresh rate is 30 seconds (30000ms)
$refresh = request()->get('refresh', 30);
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <strong>Outages</strong>
                </div>
                <template id="filter-container">
                    <form method="get" action="{{ route('outages') }}" class="form-inline tw:float-left tw:inline-block" role="form" id="result_form">
                        <div class="form-group">
                            @if($show_device_list)
                                <select name="device" id="device" class="form-control">
                                    <option value="">All Devices</option>
                                </select>
                            @else
                                <input type="hidden" name="device" id="device" value="{{ $device?->device_id }}">
                            @endif
                        </div>
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <option value="current">Current</option>
                                <option value="previous">Previous</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                        <div class="form-group tw:text-left">
                            <x-date-range-picker name="date_range" start="{{ $from }}" end="{{ $to }}" class="form-control tw:min-w-64"></x-date-range-picker>
                        </div>
                        <button type="submit" class="btn btn-default">Filter</button>
                    </form>
                </template>

                <div class="table-responsive">
                    <table id="outages" class="table table-hover table-condensed table-striped"
                           data-url="{{ route('table.outages') }}">
                        <thead>
                        <tr>
                            <th data-column-id="status" data-sortable="false"></th>
                            <th data-column-id="going_down" data-order="desc">Start</th>
                            <th data-column-id="up_again">End</th>
                            <th data-column-id="device_id">Hostname</th>
                            <th data-column-id="duration" data-sortable="false">Duration</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<x-refresh-timer :refresh="$refresh" callback="refreshOutagesGrid"></x-refresh-timer>
<script>
    // Function to refresh the outages grid
    function refreshOutagesGrid() {
        outages_grid.bootgrid('reload');
    }

    // Flag to track if user has manually set the "to" date
    var userSetToDate = {{ $to ? 'true' : 'false' }};

    var outages_grid = $("#outages").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        templates: {
            search: ""
        },
        post: function ()
        {
            // Get the hidden input values from the date-range-picker
            var fromInput = document.querySelector('input[name="from"]');
            var toInput = document.querySelector('input[name="to"]');

            var fromDate = fromInput ? fromInput.value : '';
            var toDate = toInput ? toInput.value : '';

            // If "to" date is empty or not user-set and Countdown.refreshNum > 0, use current time
            if (toDate === "" || (!userSetToDate && Countdown.refreshNum > 0)) {
                return {
                    device: $('#device').val(),
                    to: moment().format('YYYY-MM-DD HH:mm'),
                    from: fromDate,
                };
            } else {
                return {
                    device: $('#device').val(),
                    to: toDate,
                    from: fromDate,
                };
            }
        },
    }).on("loaded.rs.jquery.bootgrid", function() {
        var filterTemplate = document.getElementById("filter-container");
        var actionBar = $(".actionBar");

        if (actionBar.length) {
            while (filterTemplate.content.firstChild) {
                actionBar.prepend(filterTemplate.content.firstChild);
            }
        }
    });

    // Listen for changes to the date-range-picker
    document.addEventListener('change', function(event) {
        console.log(event.target);
        // Check if the change event is from the date-range-picker
        if (event.target.closest('div[x-data="dateRangePicker"]')) {
            // Update userSetToDate if the "to" input has a value
            var toInput = document.querySelector('input[name="to"]');
            if (toInput && toInput.value) {
                userSetToDate = true;
            }
        }
    });

    @if($show_device_list)
    init_select2("#device", "device", {}, {{ \Illuminate\Support\Js::from($selected_device) }} , "All Devices");
    $('#device').on('change', () => outages_grid.bootgrid('reload'));
    @endif
</script>
@endsection
