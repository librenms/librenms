@extends('layouts.librenmsv1')

@section('title', __('Inventory'))

@section('content')
    <div class="container-fluid">
        <x-panel body-class="!tw-p-0">
            <x-slot name="heading">
                <h3 class="panel-title">@lang('Inventory')</h3>
                @if($show_purge)
                    <div class="tw-float-right">
                        <a href="{{ route('inventory.purge') }}"><i class="fa fa-trash"></i> @lang('inventory.purge')</a>
                    </div>
                @endif
            </x-slot>

            <table id="inventory" class="table table-hover table-condensed table-striped">
                <thead>
                <tr>
                    <th data-column-id="device" data-order="asc">@lang('Device')</th>
                    <th data-column-id="descr">@lang('Description')</th>
                    <th data-column-id="name">@lang('inventory.name')</th>
                    <th data-column-id="model">@lang('inventory.model')</th>
                    <th data-column-id="serial">@lang('inventory.serial')</th>
                </tr>
                </thead>
            </table>
        </x-panel>
    </div>
@endsection

@push('scripts')
    <script>
        var grid = $("#inventory").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}} tw-flex tw-flex-wrap\">" +
                    "<form method=\"post\" action=\"\" class=\"tw-flex tw-flex-wrap tw-items-center\" role=\"form\" id=\"inventory_filter\">" +
                    "{!! addslashes(csrf_field()) !!}" +
                    "<div class=\"tw-flex tw-items-baseline tw-mr-3 tw-mt-2\">" +
                    "<span class=\"tw-mr-1\">@lang('inventory.part')</span>" +
                    "<input type=\"text\" name=\"descr\" id=\"descr\" value=\"{{ $filter['descr'] }}\" placeholder=\"@lang('Description')\" class=\"form-control\" />" +
                    "</div>" +
                    "<div class=\"tw-flex tw-items-baseline tw-mr-3 tw-mt-2\">" +
                    "<span class=\"tw-mr-1\">@lang('inventory.model')</span>" +
                    "<select name=\"model\" id=\"model\" class=\"form-control\"></select>" +
                    "</div>" +
                    "<div class=\"tw-flex tw-items-baseline tw-mr-3 tw-mt-2\">" +
                    "<input type=\"text\" name=\"serial\" id=\"serial\" value=\"{{ $filter['serial'] }}\" placeholder=\"@lang('inventory.serial')\" class=\"form-control\"/>" +
                    "</div>" +
                    "<div class=\"tw-flex tw-items-baseline tw-mr-3 tw-mt-2\">" +
                    "<span class=\"tw-mr-1\">@lang('Device')</span>" +
                    "<select name=\"device\" id=\"device\" class=\"form-control tw-ml-2\"></select>" +
                    "</div>" +
                    "<button type=\"submit\" class=\"btn btn-default tw-mr-2 tw-mt-2\">@lang('Search')</button>" +
                    "</form>" +
                    "<div class=\"actionBar tw-ml-auto tw-relative tw-mt-2\"><div class=\"@{{css.actions}}\"></div></div>" +
                    "</div>"
            },
            post: function () {
                return @json($filter)
            },
            url: "{{ route('table.inventory') }}"
        });

        <?php

        ?>

        init_select2("#model", "inventory", @json($model_filter), @json($filter['model']), "@lang('inventory.all_parts')");
        init_select2("#device", "device", {}, @json($device_selected) , "@lang('All Devices')");
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
    </style>
@endpush

