@extends('layouts.librenmsv1')

@section('title', __('Faults'))

@section('content')
    <div class="container-fluid">
        <template id="alerts-filter-template"><x-filter name="faults" :fields="$filterFields" :initial="$filter" /></template>
        <x-panel title="{{ __('Faults') }}">
            <x-slot:table>
                <div class="table-responsive">
                    <table id="alerts_" class="table table-hover table-condensed alerts">
                        <thead>
                        <tr>
                            <th data-column-id="severity"></th>
                            <th data-column-id="timestamp">{{ __('Timestamp') }}</th>
                            <th data-column-id="rule">{{ __('Rule') }}</th>
                            <th data-column-id="details" data-sortable="false"></th>
                            <th data-column-id="hostname">{{ __('Hostname') }}</th>
                            <th data-column-id="location">{{ __('Location') }}</th>
                            <th data-column-id="ack_ico" data-sortable="false">{{ __('ACK') }}</th>
                            <th data-column-id="notes" data-sortable="false">{{ __('Notes') }}</th>
                            @can('alert.detail')
                                <th data-column-id="verbose_details" data-sortable="false">{{ __('Details') }}</th>
                            @endcan
                        </tr>
                        </thead>
                    </table>
                </div>
            </x-slot:table>
        </x-panel>
    </div>

    @include('alerts.modals.details')
    @include('alerts.modals.notes')
    @include('alerts.modals.ack')
@endsection

@push('scripts')
    @include('alerts.partials.active-table-script', [
        'unique_id' => '',
        'rule_id' => '',
        'alert_id' => $alert_id ?: '',
        'acknowledged' => '',
        'fired' => '',
        'state' => '',
        'min_severity' => '',
        'group' => '',
        'proc' => '',
        'sort' => '',
        'device_id' => '',
        'filterName' => 'faults',
        'filter' => $filter,
    ])
@endpush
