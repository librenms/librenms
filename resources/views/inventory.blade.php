@extends('layouts.librenmsv1')

@section('title', __('Inventory'))

@section('content')
    <div class="container-fluid">
        <template id="inventory-filter-template"><x-filter name="inventory" :fields="$filterFields" :initial="$filter" /></template>
        <x-panel>
            <x-slot:heading class="tw:flex tw:justify-between">
                <h3 class="panel-title">{{ __('Inventory') }}</h3>
                @if($show_purge)
                    <a href="{{ route('inventory.purge') }}"><i class="fa fa-trash"></i> @lang('inventory.purge')</a>
                @endif
            </x-slot>

            <x-slot:table>
                <div class="table-responsive">
                    <table id="inventory" class="table table-hover table-condensed table-striped"
                           data-url="{{ route('table.inventory') }}" data-export-url="{{ route('table.inventory.export') }}">
                        <thead>
                        <tr>
                            <th data-column-id="device" data-order="asc">{{ __('Device') }}</th>
                            <th data-column-id="descr">{{ __('Description') }}</th>
                            <th data-column-id="name">{{ __('inventory.name') }}</th>
                            <th data-column-id="model">{{ __('inventory.model') }}</th>
                            <th data-column-id="serial">{{ __('inventory.serial') }}</th>
                            <th data-column-id="type">{{ __('Type') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </x-slot:table>
        </x-panel>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        let filter = @js($filter);
        const grid = bootgridWithExport($("#inventory"), {
            ajax: true,
            rowCount: [50, 100, 250, -1],
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"inventory-actionbar tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2\"><p class=\"@{{css.actions}}\"></p></div></div>",
                search: ""
            },
            post: function () {
                return { filter };
            }
        });

        $('#inventory-header .inventory-actionbar').prepend($('#inventory-filter-template')[0].content.cloneNode(true));

        window.addEventListener('filter:apply', function (event) {
            if (event.detail.name === 'inventory') {
                filter = event.detail.filters;
                grid.bootgrid('reload');
            }
        });
    })();
</script>
@endpush
