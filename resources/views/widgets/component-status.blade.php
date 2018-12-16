<div>
    <table id="component-status" class="table table-hover table-condensed table-striped">
        <thead>
        <tr>
            <th data-column-id="status" data-order="desc">Status</th>
            <th data-column-id="count">Count</th>
        </tr>
        </thead>
        <tbody>
        @foreach($status as $item)
            <tr>
                <td><p class="text-left {{ $item['color'] }}">{{ $item['text'] }}</p></td>
                <td><p class="text-left {{ $item['color'] }}">{{ $item['total'] }}</p></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
