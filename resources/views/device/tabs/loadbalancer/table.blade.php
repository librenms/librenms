{{-- $table: what LoadBalancerController::table() returns, $rowGraph: graph type to draw under each row --}}
<x-table striped>
    <x-slot:head>
        <tr>
            @foreach($table['headers'] as $header)
                <th class="tw:whitespace-nowrap">
                    @if($header['link'])
                        <a href="{{ $header['link'] }}">{{ $header['title'] }}</a>
                    @else
                        {{ $header['title'] }}
                    @endif
                    @if($header['order'])
                        <i class="fa fa-sort-{{ $header['order'] == 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                </th>
            @endforeach
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach($table['items'] as $row)
            <tr>
                <td><a href="{{ $link($table['section'], ['id' => $row->id]) }}">{{ $row->name }}</a></td>
                @if($table['address'])<td>{{ $row->address }}</td>@endif
                @if($table['pool'])
                    <td>
                        @if($row->poolId)
                            <a href="{{ $link(\App\Http\Controllers\Device\Tabs\LoadBalancer\LoadBalancerSection::Pools, ['id' => $row->poolId]) }}">{{ $row->pool }}</a>
                        @else
                            {{ $row->pool }}
                        @endif
                    </td>
                @endif
                @foreach($table['columns'] as $columnKey => $columnTitle)
                    <td>{{ $row->columns[$columnKey] ?? '' }}</td>
                @endforeach
                <td><x-label :status="$row->severity">{{ $row->status }}</x-label></td>
            </tr>
            @if($rowGraph ?? null)
                <tr>
                    <td colspan="{{ count($table['headers']) }}">
                        <x-graph-row :type="$rowGraph" :device="$device" :vars="['id' => $row->id]" columns="responsive" />
                    </td>
                </tr>
            @endif
        @endforeach
    </x-slot:body>
</x-table>
