
<template id="port-filter-template"><x-filter name="ports" :fields="$filterFields" id="port-filter" :hide="$hideFilter"/></template>

<div class="table-responsive">
    <table id="ports" class="table table-condensed table-hover table-striped" data-url="{{ route('table.ports') }}">
        <thead>
        <tr>
            <th data-column-id="hostname" data-formatter="device">Device</th>
            <th data-column-id="ifDescr" @if(! $errors) data-order="asc" @endif data-formatter="port">Port</th>
            <th data-column-id="secondsIfLastChange" data-converter="duration">Status Changed</th>
            <th data-column-id="ifConnectorPresent" data-visible="false">Connected</th>
            <th data-column-id="ifSpeed" data-converter="human-bps">Speed</th>
            <th data-column-id="ifDuplex" data-css-class="green" data-formatter="duplex">Duplex</th>
            <th data-column-id="ifMtu" data-visible="false">MTU</th>
            <th data-column-id="ifInOctets_rate" data-searchable="false" data-css-class="green" data-converter="human-bps">In</th>
            <th data-column-id="ifOutOctets_rate" data-searchable="false" data-css-class="blue" data-converter="human-bps">Out</th>
            <th data-column-id="ifInUcastPkts_rate" data-searchable="false" data-visible="{{ $show_detail }}" data-css-class="green" data-converter="human-pps">Packets In</th>
            <th data-column-id="ifOutUcastPkts_rate" data-searchable="false" data-visible="{{ $show_detail }}" data-css-class="blue" data-converter="human-pps">Packets Out</th>
            <th data-column-id="ifInErrors_delta" data-searchable="false" data-visible="{{ $show_errors }}" data-css-class="red">Errors In Rate</th>
            <th data-column-id="ifOutErrors_delta" data-searchable="false" data-visible="{{ $show_errors }}" data-css-class="red">Errors Out Rate</th>
            <th data-column-id="ifInErrors" data-searchable="false" data-visible="{{ $show_errors }}" data-css-class="red">Errors In</th>
            <th data-column-id="ifOutErrors" data-searchable="false" data-visible="{{ $show_errors }}" data-css-class="red">Errors Out</th>
            <th data-column-id="ifType">Media</th>
            <th data-column-id="ifAlias">Description</th>
            <th data-column-id="actions" data-sortable="false" data-searchable="false">Actions</th>
        </tr>
        </thead>
    </table>
</div>

@push('scripts')
<script>
    function formatUnits(units,decimals,display,base) {
        if(!units) return '';
        if(display === undefined) display = ['bps', 'Kbps', 'Mbps', 'Gbps', 'Tbps', 'Pbps', 'Ebps', 'Zbps', 'Ybps'];
        if(units == 0) return units + display[0];
        base = base || 1000; // or 1024 for binary
        var dm = decimals || 2;
        var i = Math.floor(Math.log(units) / Math.log(base));
        return parseFloat((units / Math.pow(base, i)).toFixed(dm)) + display[i];
    }

    var filter = @js($filter);

    var grid = $("#ports").bootgrid({
        ajax: true,
        rowCount: [25, 50, 100, 250, -1],
        converters: {
            'duration': {
                to: function (value) { return moment.duration(value, 'seconds').humanize(); }
            },
            'human-bps': {
                to: function (value) { return formatUnits(value); }
            },
            'human-pps': {
                to: function (value) {
                    return formatUnits(value, 2, ['pps', 'Kpps', 'Mpps', 'Gpps', 'Tpps', 'Ppps', 'Epps', 'Zpps', 'Ypps']);
                }
            }
        },
        formatters: {
            'device': function (column, row) {
                return "<span class='alert-status " + row.status + "' style='float:left;margin-right:10px;'></span>" + row.device + "";
            },
            'port': function (column, row) {
                return row.port
            },
            'duplex': function (column, row) {
                const duplexValue = (row.ifDuplex || '').toLowerCase().trim();
                switch (duplexValue) {
                    case 'halfduplex':
                        return "<i title='Half Duplex' data-toggle='tooltip' class='fa-solid fa-circle-half-stroke'></i>";
                    case 'fullduplex':
                        return "<i title='Full Duplex' data-toggle='tooltip' class='fa-solid fa-circle'></i>";
                    default:
                        return "<i title='No Duplex' data-toggle='tooltip' class='fa-regular fa-circle'></i>";
                }
            }
        },
        templates: {
            search: "" // hide the generic search
        },
        post: function () {
            return {
                filter: filter
            };
        },
        requestHandler: function (request) {
            @if($errors)
            if (request.sort === undefined || Object.keys(request.sort).length === 0) {
                request.sort = { errors: 'desc'};
            }
            @endif
            return request;
        }
    });

    const $template = $('#port-filter-template');
    if ($template.length) {
        const $content = $($template[0].content.cloneNode(true));

        const $wrapper = $('<div class="pull-left"></div>');
        $wrapper.append($content);

        $(".actionBar").append($wrapper);
    }

    $(window).on('filter:apply', function (event) {
        filter = event.originalEvent.detail.filters;
        grid.bootgrid('reload');
    });
</script>
@endpush
