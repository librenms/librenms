@extends('layouts.librenmsv1')

@section('title', __('Outages'))

@php
$refresh = request()->get('refresh', 30);
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-lg-8 col-lg-offset-2">
            <div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <strong>{{ __('Outages') }}</strong>
                </div>
                <template id="filter-container">
                    <form id="filter-form" method="get" action="{{ route('outages') }}" class="form-inline tw:float-left tw:inline-block" role="form">
                        <div class="form-group">
                            @if($show_device_list)
                                <select name="device" id="device" class="form-control">
                                    <option value="">{{ __('device.all_devices') }}</option>
                                </select>
                            @else
                                <input type="hidden" name="device" id="device" value="{{ $device?->device_id }}">
                            @endif
                        </div>
                        <div class="form-group">
                            <select id="status" name="status" class="form-control">
                                <option value="current" {{ $status == 'current' ? 'selected' : '' }}>{{ __('Current') }}</option>
                                <option value="previous" {{ $status == 'previous' ? 'selected' : '' }}>{{ __('Previous') }}</option>
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                            </select>
                        </div>
                        <div class="form-group tw:text-left">
                            <x-date-range-picker
                                id="date_range" name="date_range"
                                start="{{ $from }}"
                                end="{{ $to }}"
                                output-format="timestamp"
                                class="form-control tw:min-w-64"
                                x-on:date-range-changed="refreshOutagesGrid"
                            ></x-date-range-picker>
                        </div>
                        <button type="button" id="apply-filters" class="btn btn-default">{{ __('Filter') }}</button>
                    </form>
                </template>

                <div class="table-responsive">
                    <table id="outages" class="table table-hover table-condensed table-striped"
                           data-url="{{ route('table.outages') }}">
                        <thead>
                        <tr>
                            <th data-column-id="status" data-sortable="false" data-visible-in-selection="false"></th>
                            <th data-column-id="going_down" data-order="desc">{{ __('Start') }}</th>
                            <th data-column-id="up_again" data-visible="{{ $status == 'current' ? 'false' : 'true' }}">{{ __('End') }}</th>
                            <th data-column-id="device_id">{{ __('device.attributes.hostname') }}</th>
                            <th data-column-id="duration" data-sortable="false">{{ __('Duration') }}</th>
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
    function refreshOutagesGrid() {
        outages_grid.bootgrid('reload');
    }

    var outages_grid = $("#outages").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        templates: {
            search: ""
        },
        post: function ()
        {
            const picker = document.querySelector('#date_range');

            if (!picker) {
                return {
                    device: @js($device?->device_id),
                    status: @js($status),
                    from: @js($from),
                    to: @js($to),
                };
            }

            const range = picker.dateRangePicker.get();
            return {
                device: document.getElementById('device').value,
                status: document.getElementById('status').value,
                to: range.end?.toISOString(),
                from: range.start?.toISOString(),
            };
        },
    }).on("loaded.rs.jquery.bootgrid", function() {
        var filterTemplate = document.getElementById("filter-container");

        if (filterTemplate.content.hasChildNodes()) {
            var actionBar = document.querySelector(".actionBar");

            while (filterTemplate.content.firstChild) {
                actionBar.insertBefore(filterTemplate.content.firstChild, actionBar.firstChild);
            }

            // init the filter bar js
            document.getElementById("status").addEventListener("change", refreshOutagesGrid);
            @if($show_device_list)
            init_select2("#device", "device", {}, {{ \Illuminate\Support\Js::from($selected_device) }} , "{{ __('device.all_devices') }}");
            $('#device').on('change', refreshOutagesGrid);
            @endif

            // update url on filter click
            document.getElementById('apply-filters').addEventListener('click', function (e) {
                let params = new URLSearchParams(window.location.search);
                const before = params.toString();
                const formData = new FormData(document.getElementById('filter-form'));

                for (let [key, value] of formData.entries()) {
                    value ? params.set(key, value) : params.delete(key);
                }

                const after = params.toString();

                // Only update the URL and refresh if something actually changed
                if (before !== after) {
                    let newUrl = new URL(window.location.pathname, window.location.origin);
                    newUrl.search = after;
                    window.history.pushState({}, '', newUrl);
                    refreshOutagesGrid();
                }
            });
        }
    });
</script>
@endsection
