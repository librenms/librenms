<table>
    @foreach($actions as $row)
        <tr>
            @foreach($row as $action)
                @include('device.action-icon', $action)
            @endforeach
        </tr>
    @endforeach
</table>
