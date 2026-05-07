@extends('layouts.librenmsv1')

@section('title', __('Inventory'))

@section('content')
    <div class="container-fluid">
        <x-panel>
            <x-slot:heading class="tw:flex tw:justify-between">
                <h3 class="panel-title">{{ __('Inventory') }}</h3>
                @if($show_purge)
                        <a href="{{ route('inventory.purge') }}"><i class="fa fa-trash"></i> @lang('inventory.purge')</a>
                @endif
            </x-slot>

            <x-slot:slot class="tw:p-0!">
            <table id="inventory" class="table table-hover table-condensed table-striped"
                data-url="{{ route('table.inventory') }}">
                <thead>
                <tr>
                    <th data-column-id="device" data-order="asc">{{ __('Device') }}</th>
                    <th data-column-id="descr">{{ __('Description') }}</th>
                    <th data-column-id="name">{{ __('inventory.name') }}</th>
                    <th data-column-id="model">{{ __('inventory.model') }}</th>
                    <th data-column-id="serial">{{ __('inventory.serial') }}</th>
                </tr>
                </thead>
            </table>
            </x-slot:slot>
        </x-panel>
    </div>
@endsection

@push('scripts')
    <script>
        var grid = $("#inventory").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}} tw:flex tw:flex-wrap\">" +
                    "<form method=\"post\" action=\"\" class=\"tw:flex tw:flex-wrap tw:items-center\" role=\"form\" id=\"inventory_filter\">" +
                    "{!! addslashes(csrf_field()) !!}" +
                    "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                    "<span class=\"tw:mr-1\">{{ __('inventory.part') }}</span>" +
                    "<input type=\"text\" name=\"descr\" id=\"descr\" value=\"{{ $filter['descr'] }}\" placeholder=\"{{ __('Description') }}\" class=\"form-control\" />" +
                    "</div>" +
                    "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                    "<span class=\"tw:mr-1\">{{ __('inventory.model') }}</span>" +
                    "<select name=\"model\" id=\"model\" class=\"form-control\"></select>" +
                    "</div>" +
                    "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                    "<input type=\"text\" name=\"serial\" id=\"serial\" value=\"{{ $filter['serial'] }}\" placeholder=\"{{ __('inventory.serial') }}\" class=\"form-control\"/>" +
                    "</div>" +
                    "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                    "<span class=\"tw:mr-1\">{{ __('Device') }}</span>" +
                    "<select name=\"device\" id=\"device\" class=\"form-control tw:ml-2\"></select>" +
                    "</div>" +
                    "<button type=\"submit\" class=\"btn btn-default tw:mr-2 tw:mt-2\">{{ __('Search') }}</button>" +
                    "</form>" +
                    "<div class=\"actionBar tw:ml-auto tw:relative tw:mt-2\"><div class=\"@{{css.actions}}\"></div></div>" +
                    "</div>"
            },
            post: function () {
                return @json($filter)
            },
        });

        <?php

        ?>

        init_select2("#model", "inventory", @json($model_filter), @json($filter['model']), "{{ __('inventory.all_parts') }}");
        init_select2("#device", "device", {}, @json($device_selected) , "{{ __('device.all_devices') }}");
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

