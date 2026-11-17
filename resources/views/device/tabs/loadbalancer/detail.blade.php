<div class="row">
    <div @class(['col-md-6' => $members, 'col-md-12' => ! $members])>
        <x-panel>
            <x-slot:heading>
                <h3 class="panel-title">{{ $item->name }} <x-label :status="$item->severity">{{ $item->status }}</x-label></h3>
            </x-slot:heading>
            @if($item->address !== null || $item->pool !== null || $item->columns || $item->details)
                <x-slot:table>
                    <table class="table table-condensed">
                        @if($item->address !== null)
                            <tr><th class="tw:w-1/3">{{ __('Address') }}</th><td>{{ $item->address }}</td></tr>
                        @endif
                        @if($item->pool !== null)
                            <tr>
                                <th class="tw:w-1/3">{{ __('Pool') }}</th>
                                <td>
                                    @if($item->poolId)
                                        <a href="{{ $link(\App\Http\Controllers\Device\Tabs\LoadBalancer\LoadBalancerSection::Pools, ['id' => $item->poolId]) }}">{{ $item->pool }}</a>
                                    @else
                                        {{ $item->pool }}
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @foreach($columns as $columnKey => $columnTitle)
                            @isset($item->columns[$columnKey])
                                <tr><th class="tw:w-1/3">{{ $columnTitle }}</th><td>{{ $item->columns[$columnKey] }}</td></tr>
                            @endisset
                        @endforeach
                        @foreach($item->details as $title => $value)
                            <tr><th class="tw:w-1/3">{{ $title }}</th><td>{{ $value }}</td></tr>
                        @endforeach
                    </table>
                </x-slot:table>
            @endif
        </x-panel>
    </div>
    @if($members)
        <div class="col-md-6">
            <x-panel :title="__('Pool Members') . ' (' . count($members['items']) . ')'">
                <x-slot:table>
                    @include('device.tabs.loadbalancer.table', ['table' => $members, 'rowGraph' => null])
                </x-slot:table>
            </x-panel>
        </div>
    @endif
</div>
