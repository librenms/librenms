@extends('layouts.librenmsv1')

@section('title', __('Billing'))

@section('content')
<div class="container-fluid" x-data="{ createBill: @js($port !== null || $errors->any()) }" x-on:create-bill.window="createBill = true">
    <x-panel class="panel-condensed">
        <x-slot name="table">
            <div class="table-responsive">
                <table class="table table-hover" id="bills-list">
                    <thead>
                    <tr>
                        <th data-column-id="bill_name">{{ __('Billing name') }}</th>
                        <th data-column-id="notes" data-sortable="false"></th>
                        <th data-column-id="bill_type">{{ __('Type') }}</th>
                        <th data-column-id="bill_allowed" data-align="right">{{ __('Allowed') }}</th>
                        <th data-column-id="total_data_in" data-align="right">{{ __('Inbound') }}</th>
                        <th data-column-id="total_data_out" data-align="right">{{ __('Outbound') }}</th>
                        <th data-column-id="total_data" data-align="right">{{ __('Total') }}</th>
                        <th data-column-id="rate_95th" data-align="right">{{ __('95th Percentile') }}</th>
                        <th data-column-id="overusage" data-sortable="false" data-align="center">{{ __('Overusage') }}</th>
                        <th data-column-id="predicted" data-sortable="false" data-align="center">{{ __('Predicted') }}</th>
                        <th data-column-id="graph" data-sortable="false"></th>
                        <th data-column-id="actions" data-sortable="false"></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </x-slot>
    </x-panel>

    @can('create', \App\Models\Bill::class)
        <x-modal show="createBill" :title="__('Add Traffic Bill')" maxWidth="2xl">
            <form method="post" action="{{ route('bill.store') }}" class="form-horizontal">
                @csrf
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="device">{{ __('Device') }}</label>
                    <div class="col-sm-8">
                        <select class="form-control input-sm" id="device"></select>
                    </div>
                </div>
                <div class="form-group @error('port_id') has-error @enderror">
                    <label class="col-sm-4 control-label" for="port_id">{{ __('Port') }}</label>
                    <div class="col-sm-8">
                        <select class="form-control input-sm" id="port_id" name="port_id"></select>
                        <span class="help-block">{{ $errors->first('port_id') }}</span>
                    </div>
                </div>
                @include('bill.form', ['bill' => $newBill])
                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-8">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{ __('Add Bill') }}</button>
                    </div>
                </div>
            </form>
        </x-modal>
    @endcan
</div>

<script type="text/html" id="table-header">
    <div id="@{{ctx.id}}" class="@{{css.header}}">
        <div class="row">
            <div class="col-sm-4">
                @can('create', \App\Models\Bill::class)
                    <button type="button" class="btn btn-default btn-sm" onclick="window.dispatchEvent(new CustomEvent('create-bill'))"><i class="fa fa-plus"></i> {{ __('Create Bill') }}</button>
                @endcan
            </div>
            <div class="col-sm-8 actionBar">
                <span class="form-inline" id="table-filters">
                    <select name="period" id="period" class="form-control input-sm">
                        <option value="">{{ __('Current Billing Period') }}</option>
                        <option value="prev">{{ __('Previous Billing Period') }}</option>
                    </select>
                    <select name="bill_type" id="bill_type" class="form-control input-sm">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="cdr" @selected($filters['bill_type'] === 'cdr')>{{ __('CDR') }}</option>
                        <option value="quota" @selected($filters['bill_type'] === 'quota')>{{ __('Quota') }}</option>
                    </select>
                    <select name="state" id="state" class="form-control input-sm">
                        <option value="">{{ __('All States') }}</option>
                        <option value="under" @selected($filters['state'] === 'under')>{{ __('Under Quota') }}</option>
                        <option value="over" @selected($filters['state'] === 'over')>{{ __('Over Quota') }}</option>
                    </select>
                </span>
                <p class="@{{css.search}}"></p>
                <p class="@{{css.actions}}"></p>
            </div>
        </div>
    </div>
</script>
@endsection

@section('javascript')
<script>
    $(function () {
        const grid = $('#bills-list').bootgrid({
            ajax: true,
            templates: {
                header: $('#table-header').html()
            },
            columnSelection: false,
            rowCount: [50, 100, 250, -1],
            post: function () {
                return {
                    bill_type: $('#bill_type').val(),
                    state: $('#state').val(),
                    period: $('#period').val()
                };
            },
            url: "{{ route('table.bills') }}"
        });
        $(document).on('change', '#table-filters select', function () {
            grid.bootgrid('reload');
        });

        @can('create', \App\Models\Bill::class)
        init_select2('#device', 'device', {}, @js($port?->device ? ['id' => $port->device_id, 'text' => $port->device->display] : null), @js(__('Select Device')), {width: '100%'});
        init_select2('#port_id', 'port', function (params) {
            params.device = $('#device').val();
            return params;
        }, @js($port ? ['id' => $port->port_id, 'text' => $port->getLabel()] : null), @js(__('Select Port')), {width: '100%'});
        $('#device').on('change', function () {
            $('#port_id').val(null).trigger('change');
        });
        @endcan
    });
</script>
@endsection
