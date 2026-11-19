<x-table :rows="$rows" :empty="__('No CEF switching entries found.')" class="tw:border-collapse">
    <x-slot:head>
        <tr>
            @if($showDevice)
                <th>{{ __('Device') }}</th>
            @endif
            <th><span title="{{ __('Physical hardware entity') }}">{{ __('Entity') }}</span></th>
            <th><span title="{{ __('Address Family') }}">{{ __('AFI') }}</span></th>
            <th><span title="{{ __('CEF Switching Path') }}">{{ __('Path') }}</span></th>
            <th><span title="{{ __('Number of packets dropped.') }}">{{ __('Drop') }}</span></th>
            <th><span title="{{ __('Number of packets that could not be switched in the normal path and were punted to the next-fastest switching vector.') }}">{{ __('Punt') }}</span></th>
            <th><span title="{{ __('Number of packets that could not be switched in the normal path and were punted to the host.') }}">{{ __('Punt2Host') }}</span></th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach($rows as $row)
            <tr>
                @if($showDevice)
                    <td><x-device-link :device="$row['cef']->device" tab="routing" section="cef" /></td>
                @endif
                <td>{{ $row['entity_descr'] }}</td>
                <td>{{ $row['cef']->afi }}</td>
                <td>
                    @if($row['path_title'])
                        <span title="{{ $row['path_title'] }}">{{ $row['cef']->cef_path }}</span>
                    @else
                        {{ $row['cef']->cef_path }}
                    @endif
                </td>
                @foreach(['drop', 'punt', 'punt2host'] as $counter)
                    <td>
                        {{ $row[$counter] }}
                        @if($row[$counter . '_rate'] !== null)
                            <span class="tw:text-red-600 dark:tw:text-red-400">({{ $row[$counter . '_rate'] }}/sec)</span>
                        @endif
                    </td>
                @endforeach
            </tr>

            @if($graphs)
                <tr>
                    <td colspan="{{ $showDevice ? 7 : 6 }}">
                        <x-graph-row type="cefswitching_graph" :vars="['id' => $row['cef']->cef_switching_id]" />
                    </td>
                </tr>
            @endif
        @endforeach
    </x-slot:body>
</x-table>
