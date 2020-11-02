@extends('layouts.librenmsv1')

@section('title', __('Virtual Machines'))

@section('content')
<div class="table-responsive">
    <table id="vminfo" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="deviceid" data-visible="false" data-css-class="deviceid">No</th>
                <th data-column-id="sysname" data-visible="false">Sysname</th>
                <th data-column-id="vmwVmDisplayName">@lang('Device')</th>
                <th data-column-id="vmwVmState">@lang('Power Status')</th>
                <th data-column-id="hostname">@lang('Host')</th>
                <th data-column-id="vmwVmGuestOS" data-searchable="false">@lang('Operating System')</th>
                <th data-column-id="vmwVmMemSize" data-searchable="false">@lang('Memory')</th>
                <th data-column-id="vmwVmCpus" data-searchable="false">@lang('CPU')</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
    $("#vminfo").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        url: "{{ url('/ajax/table/vminfo') }}"
    });
</script>
@endsection
