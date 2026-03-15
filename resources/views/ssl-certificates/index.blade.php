@extends('layouts.librenmsv1')

@section('title', __('SSL Certificates'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <x-panel title="{{ __('SSL Certificates') }}" id="ssl-certificates-panel">
                @can('create', \App\Models\SslCertificate::class)
                <p style="margin: 10px 15px 0;">
                    <a href="{{ route('ssl-certificates.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus fa-fw" aria-hidden="true"></i> {{ __('Add Certificate') }}
                    </a>
                </p>
                @endcan
                <div class="table-responsive">
                    <table id="ssl-certificates" class="table table-hover table-condensed table-striped">
                        <thead>
                        <tr>
                            <th data-column-id="host" data-order="asc">{{ __('Host') }}</th>
                            <th data-column-id="port">{{ __('Port') }}</th>
                            <th data-column-id="subject">{{ __('Subject') }}</th>
                            <th data-column-id="issuer">{{ __('Issuer') }}</th>
                            <th data-column-id="valid_to">{{ __('Valid Until') }}</th>
                            <th data-column-id="days_until_expiry">{{ __('Days Until Expiry') }}</th>
                            <th data-column-id="device" data-sortable="false">{{ __('Device') }}</th>
                            <th data-column-id="status" data-sortable="false">{{ __('Status') }}</th>
                            <th data-column-id="actions" data-formatter="actions" data-sortable="false">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </x-panel>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
var sslCertUpdateUrl = "{{ route('ssl-certificates.update', ['ssl_certificate' => '__ID__']) }}".replace('__ID__', '');
var sslCertDestroyUrl = "{{ route('ssl-certificates.destroy', ['ssl_certificate' => '__ID__']) }}".replace('__ID__', '');

function sslCertPause(id) {
    var url = sslCertUpdateUrl + id;
    $.ajax({
        method: 'PUT',
        url: url,
        headers: { 'Accept': 'application/json' },
        data: { disabled: 1 }
    }).done(function () {
        $("#ssl-certificates").bootgrid('reload');
        toastr.success("{{ __('Certificate paused') }}");
    }).fail(function (xhr) {
        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "{{ __('Failed to pause certificate') }}");
    });
}

function sslCertEnable(id) {
    var url = sslCertUpdateUrl + id;
    $.ajax({
        method: 'PUT',
        url: url,
        headers: { 'Accept': 'application/json' },
        data: { disabled: 0 }
    }).done(function () {
        $("#ssl-certificates").bootgrid('reload');
        toastr.success("{{ __('Certificate enabled') }}");
    }).fail(function (xhr) {
        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "{{ __('Failed to enable certificate') }}");
    });
}

function sslCertDelete(id) {
    if (!confirm("{{ __('Delete this certificate?') }}")) {
        return;
    }
    var url = sslCertDestroyUrl + id;
    $.ajax({
        method: 'DELETE',
        url: url,
        headers: { 'Accept': 'application/json' }
    }).done(function () {
        $("#ssl-certificates").bootgrid('reload');
        toastr.success("{{ __('Certificate deleted') }}");
    }).fail(function (xhr) {
        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "{{ __('Failed to delete certificate') }}");
    });
}

$(document).ready(function () {
    $("#ssl-certificates").bootgrid({
        ajax: true,
        rowCount: [25, 50, 100, -1],
        url: "{{ route('table.ssl-certificates') }}",
        formatters: {
            "actions": function (column, row) {
                var html = '<div class="ssl-cert-actions" style="white-space:nowrap">';
                html += '<a href="{{ url('ssl-certificates') }}/' + row.id + '" class="btn btn-xs btn-info"><i class="fa fa-eye fa-fw"></i> {{ __('View') }}</a> ';
                @can('update', \App\Models\SslCertificate::class)
                if (row.disabled) {
                    html += '<button type="button" class="btn btn-xs btn-success" onclick="sslCertEnable(' + row.id + ')"><i class="fa fa-play fa-fw"></i> {{ __('Enable') }}</button> ';
                } else {
                    html += '<button type="button" class="btn btn-xs btn-warning" onclick="sslCertPause(' + row.id + ')"><i class="fa fa-pause fa-fw"></i> {{ __('Pause') }}</button> ';
                }
                @endcan
                @can('delete', \App\Models\SslCertificate::class)
                html += '<button type="button" class="btn btn-xs btn-danger" onclick="sslCertDelete(' + row.id + ')"><i class="fa fa-trash fa-fw"></i> {{ __('Delete') }}</button>';
                @endcan
                html += '</div>';
                return html;
            }
        }
    });
});
</script>
@endsection
