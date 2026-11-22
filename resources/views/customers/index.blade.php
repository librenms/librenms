@extends('layouts.librenmsv1')

@section('title', __('Customers'))

@section('content')
<div class="container-fluid">
    <x-panel>
        <x-slot:table>
            <div class="table-responsive">
                <table id="customers" class="table table-hover table-condensed" data-url="{{ route('table.customers') }}">
                    <thead>
                        <tr>
                            <th data-column-id="port_descr_descr" data-order="asc" data-formatter="customer">{{ __('Customer') }}</th>
                            <th data-column-id="hostname">{{ __('Device') }}</th>
                            <th data-column-id="ifDescr">{{ __('Interface') }}</th>
                            <th data-column-id="port_descr_speed">{{ __('Speed') }}</th>
                            <th data-column-id="port_descr_circuit">{{ __('Circuit') }}</th>
                            <th data-column-id="port_descr_notes">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </x-slot:table>
    </x-panel>
</div>
@endsection

@section('scripts')
<script>
    $("#customers").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        formatters: {
            customer: function (column, row) {
                return '<strong>' + row[column.id] + '</strong>';
            }
        }
    });
</script>
@endsection
