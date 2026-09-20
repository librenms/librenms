@extends('layouts.librenmsv1')

@section('title', __('Virtual Machines'))

@section('content')
<div class="container-fluid">
    <template id="vminfo-filter-template"><x-filter name="vminfo" :fields="$filterFields" :initial="$filter"/></template>
    <x-panel>
        <x-slot:table>
            <div class="table-responsive">
                <table id="vminfo" class="table table-hover table-condensed table-striped"
                       data-url="{{ route('table.vminfo') }}">
                    <thead>
                        <tr>
                            <th data-column-id="deviceid" data-visible="false">{{ __('Device Id') }}</th>
                            <th data-column-id="sysname" data-visible="false">{{ __('Sysname') }}</th>
                            <th data-column-id="vmwVmDisplayName" data-order="asc">{{ __('VM Name') }}</th>
                            <th data-column-id="hostname">{{ __('Host') }}</th>
                            <th data-column-id="vmwVmState">{{ __('Power Status') }}</th>
                            <th data-column-id="vm_type">{{ __('Type') }}</th>
                            <th data-column-id="vmwVmGuestOS" data-searchable="false">{{ __('Operating System') }}</th>
                            <th data-column-id="vmwVmMemSize" data-searchable="false">{{ __('Memory') }}</th>
                            <th data-column-id="vmwVmCpus" data-searchable="false">{{ __('vCPUs') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </x-slot:table>
    </x-panel>
</div>
@endsection

@section('scripts')
<script>
    var filter = @js($filter);

    var grid = $("#vminfo").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        templates: {
            header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"actionBar tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2\"><p class=\"@{{css.actions}}\"></p></div></div>",
            search: ""
        },
        post: function () {
            return {
                filter: filter
            }
        }
    })

    const $template = $('#vminfo-filter-template');
    if ($template.length) {
        const $content = $($template[0].content.cloneNode(true));
        $(".actionBar").prepend($content);
    }

    $(window).on('filter:apply', function (event) {
        if (event.originalEvent.detail.name === 'vminfo') {
            filter = event.originalEvent.detail.filters;
            grid.bootgrid('reload');
        }
    })
</script>
@endsection
