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
    <h4>{{ __('No devices found within interval.') }}</h4>
@endif
