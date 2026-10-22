@extends('bill.layout')

@section('bill-content')
    <h3>{{ __('Transfer Report') }}</h3>
    <p><strong>{{ __('Billing Period from :from to :to', ['from' => $period['from']->format($dateFormat), 'to' => $period['to']->format($dateFormat)]) }}</strong></p>

    <div class="row">
        <div class="col-lg-5 col-lg-push-7">
            @include('bill.ports')
        </div>
        <div class="col-lg-7 col-lg-pull-5">
            <x-panel :title="__('Bill Summary')">
                <x-slot name="table">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{{ __('Bandwidth') }}</th>
                                <th>{{ __('Used') }}</th>
                                <th>{{ __('Allowed') }}</th>
                                <th>{{ __('Average') }}</th>
                                <th>{{ __('Estimated') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <th>{{ $row['label'] }}</th>
                                    <td>{{ $row['used'] }}</td>
                                    <td>{{ $row['allowed'] }}</td>
                                    <td>{{ $row['average'] }}</td>
                                    <td>{{ $row['estimated'] }}</td>
                                    <td>{!! \LibreNMS\Util\Html::percentageBar(200, 10, $row['percent'], right_text: $row['percent'] . '%') !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-slot>
            </x-panel>
        </div>
    </div>

    @foreach($graphs as $title => $vars)
        <x-panel :title="$title" class="tw:overflow-x-auto">
            <x-graph type="bill_historictransfer" :vars="['id' => $bill->bill_id, 'imgtype' => $vars['imgtype']]" :from="$vars['from']" :to="$vars['to']"
                     :width="1190" :height="250" :link="false" loading="lazy" />
        </x-panel>
    @endforeach
@endsection
