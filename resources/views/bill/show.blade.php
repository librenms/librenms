@extends('bill.layout')

@section('bill-content')
    <h3>{{ $bill->isCdr() ? __('CDR / 95th Bill') : __('Quota Bill') }}</h3>
    <p><strong>{{ __('Billing Period from :from to :to', ['from' => $period['from']->format($dateFormat), 'to' => $period['to']->format($dateFormat)]) }}</strong></p>

    <div class="row">
        <div class="col-lg-6 col-lg-push-6">
            @include('bill.ports')
        </div>
        <div class="col-lg-6 col-lg-pull-6">
            <x-panel :title="__('Bill Summary')">
                <x-slot name="table">
                    <table class="table">
                        <tr>
                            <td>
                                {{ $bill->formatUsage($bill->used()) }} {{ __('of') }} {{ $bill->formatUsage($bill->allowed()) }} ({{ $percent }}%)
                                @if($bill->isCdr())
                                    ({{ __('95th %ile') }})
                                @else
                                    - {{ __('Average rate') }} {{ \LibreNMS\Util\Number::formatSi($bill->rate_average, 2, 0, 'bps') }}
                                @endif
                            </td>
                            <td style="width: 210px;">{!! \LibreNMS\Util\Html::percentageBar(200, 10, $percent, right_text: $percent . '%') !!}</td>
                        </tr>
                        <tr>
                            <td colspan="2">{{ __('Predicted usage') }}: {{ $bill->formatUsage($predicted) }}</td>
                        </tr>
                    </table>
                </x-slot>
            </x-panel>
        </div>
    </div>

    @foreach($graphs as $title => $range)
        <x-panel :title="$title" class="tw:overflow-x-auto">
            <x-graph :type="$graph['type']" :vars="$graph['vars']" :from="$range['from']" :to="$range['to']"
                     :width="$graph['width']" :height="$graph['height']" :link="false" legend="yes" loading="lazy" />
        </x-panel>
    @endforeach
@endsection
