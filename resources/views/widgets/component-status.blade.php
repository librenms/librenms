<div class="lnms-component-status">
    <table id="component-status" class="table table-hover table-condensed table-striped">
        <thead>
        <tr>
            <th data-column-id="status" data-order="desc">{{ __('Status') }}</th>
            <th data-column-id="count">{{ __('Count') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($status as $item)
            <tr>
                <td><span class="text-left {{ $item['color'] }}">{{ $item['text'] }}</span></td>
                <td><span class="text-left {{ $item['color'] }}">{{ $item['total'] }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
