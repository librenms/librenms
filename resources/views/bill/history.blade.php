@extends('bill.layout')

@section('bill-content')
    <h3>{{ __('Historical Usage') }}</h3>

    <x-panel :title="__('Monthly Usage')" class="tw:overflow-x-auto">
        <x-graph type="bill_historicmonthly" :vars="['id' => $bill->bill_id]" :width="1190" :height="250" :link="false" />
    </x-panel>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>{{ __('Period') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Allowed') }}</th>
                <th>{{ __('Inbound') }}</th>
                <th>{{ __('Outbound') }}</th>
                <th>{{ __('Peak Out') }}</th>
                <th>{{ __('Peak In') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('95th %ile') }}</th>
                <th class="text-center">{{ __('Overusage') }}</th>
                <th colspan="2" class="text-right">
                    <a href="{{ route('bill.history', ['bill' => $bill, 'detail' => 'all']) }}">
                        <i class="fa fa-bar-chart fa-lg icon-theme" aria-hidden="true"></i> {{ __('Show details') }}
                    </a>
                </th>
            </tr>
            </thead>
            <tbody>
            @forelse($history as $entry)
                @php($colors = \LibreNMS\Util\Color::percentage($entry['percent']))
                <tr>
                    <td class="tw:font-bold tw:whitespace-nowrap">{{ $entry['from']->format('Y-m-d') }} {{ __('to') }} {{ $entry['to']->format('Y-m-d') }}</td>
                    <td>{{ $entry['type'] }}</td>
                    <td>{{ $entry['allowed'] }}</td>
                    <td>{{ $entry['in'] }}</td>
                    <td>{{ $entry['out'] }}</td>
                    <td>{{ $entry['peak_out'] }}</td>
                    <td>{{ $entry['peak_in'] }}</td>
                    <td @class(['tw:font-bold' => ! $entry['is_cdr']])>{{ $entry['total'] }}</td>
                    <td @class(['tw:font-bold' => $entry['is_cdr']])>{{ $entry['rate_95th'] }}</td>
                    <td class="text-center">
                        @if($entry['overuse'])
                            <span class="tw:font-bold" style="color: #{{ $colors['left'] }}">{{ $entry['overuse'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="width: 250px">{!! \LibreNMS\Util\Html::percentageBar(250, 10, $entry['percent'], null, $entry['percent'] . '%', null, null, ['left' => $colors['left'], 'right' => $colors['right']]) !!}</td>
                    <td>
                        <a href="{{ route('bill.history', ['bill' => $bill, 'detail' => $entry['id']]) }}" title="{{ __('Show details') }}">
                            <i class="fa fa-bar-chart fa-lg icon-theme" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
                @if($entry['show_detail'])
                    <tr>
                        <td colspan="12" class="tw:overflow-x-auto">
                            <x-graph type="bill_historicbits" :vars="['id' => $bill->bill_id, 'bill_hist_id' => $entry['id']]" :width="1190" :height="250" :link="false" loading="lazy" />
                            <x-graph type="bill_historictransfer" :vars="['id' => $bill->bill_id, 'bill_hist_id' => $entry['id'], 'imgtype' => 'day']" :width="1190" :height="250" :link="false" loading="lazy" />
                            <x-graph type="bill_historictransfer" :vars="['id' => $bill->bill_id, 'bill_hist_id' => $entry['id'], 'imgtype' => 'hour']" :width="1190" :height="250" :link="false" loading="lazy" />
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="12">{{ __('No billing history') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
