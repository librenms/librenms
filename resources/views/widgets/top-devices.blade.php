<div class="lnms-top-devices">
@if($rows->isNotEmpty())
<div class="table-responsive">
    <table class="table table-hover table-condensed table-striped bootgrid-table">
        <thead>
        <tr>
            <th class="text-left">Device</th>
            @foreach($headers as $header)
                <th class="text-left">{{ $header }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                @foreach($row as $column)
                    <td class="text-left">{!! $column !!}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@else
    {{-- Controller normally returns needs-config; keep quiet empty if view is hit directly --}}
    <div class="lnms-widget-needs-config" data-widget-type="top-devices">
        <p class="lnms-widget-needs-config__msg">{{ __('No devices found within interval.') }}</p>
    </div>
@endif
</div>
