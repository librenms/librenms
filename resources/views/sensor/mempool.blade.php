@extends('layouts.librenmsv1')

@section('title', $title)

@section('content')
    <div class="lnms-health">
        <div class="panel panel-default panel-condensed lnms-health-panel">
            <div class="panel-heading">
                <div class="lnms-health-toolbar">
                    <div class="lnms-health-toolbar__left">
                        <x-option-bar border="none" name="{{ __('Health') }}" :options="$metrics" :selected="$metric"></x-option-bar>
                        <x-option-bar border="none" name="{{ __('Status') }}" :options="$status_bar" :selected="$status"></x-option-bar>
                    </div>
                    <div class="lnms-health-toolbar__right">
                        <x-option-bar border="none" :options="$views" :selected="$view"></x-option-bar>
                    </div>
                </div>
            </div>
            <div class="table-responsive lnms-health-list">
                <table id="mempool" class="table table-hover table-condensed"
                       data-url="{{ route('table.mempools') }}">
                    <thead>
                    <tr>
                        <th data-column-id="hostname">{{ __('Device') }}</th>
                        <th data-column-id="mempool_descr">{{ $view == 'graphs' ? '' : __('Memory') }}</th>
                        <th data-column-id="graph" data-sortable="false" data-searchable="false"></th>
                        <th data-column-id="mempool_used" data-searchable="false">{{ $view == 'graphs' ? '' : __('Used') }}</th>
                        <th data-column-id="mempool_perc" data-searchable="false">{{ $view == 'graphs' ? '' : __('Usage') }}</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <script>
        var grid = $("#mempool").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            post: function ()
            {
                return {
                    view: '{{ $view }}',
                    status: '{{ $status }}',

                };
            }
        });
    </script>
@endsection
