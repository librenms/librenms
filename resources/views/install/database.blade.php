@extends('layouts.install')

@section('title', trans('install.database.title'))

@section('content')
    <div class="row">
        <div class="col-12">
            <form id="database-form" class="form-horizontal" role="form" method="post" action="{{ route('install.acton.test-database') }}">
                @csrf
                <div class="form-row pb-3">
                    <label for="host" class="col-4 col-form-label text-right">@lang('install.database.host')</label>
                    <div class="col-6">
                        <input type="text" class="form-control" name="host" id="host" value="{{ $host ?? 'localhost' }}" placeholder="@lang('install.database.socket_empty')">
                    </div>
                </div>
                <div class="form-row pb-3">
                    <label for="port" class="col-4 col-form-label text-right">@lang('install.database.port')</label>
                    <div class="col-6">
                        <input type="text" class="form-control" name="port" id="port" value="{{ $port ?? 3306 }}" placeholder="@lang('install.database.socket_empty')">
                    </div>
                </div>
                <div class="form-row pb-3">
                    <label for="unix_socket" class="col-4 col-form-label text-right">@lang('install.database.socket')</label>
                    <div class="col-6">
                        <input type="text" class="form-control" name="unix_socket" id="unix_socket" value="{{ $unix_socket ?? '' }}" placeholder="@lang('install.database.ip_empty')">
                    </div>
                </div>
                <div class="form-row pb-3">
                    <label for="username" class="col-4 col-form-label text-right">@lang('install.database.username')</label>
                    <div class="col-6">
                        <input type="text" class="form-control" name="username" id="username" value="{{ $username ?? 'librenms' }}">
                    </div>
                </div>
                <div class="form-row pb-3">
                    <label for="password" class="col-4 col-form-label text-right">@lang('install.database.password')</label>
                    <div class="col-6">
                        <input type="password" class="form-control" name="password" id="password" value="{{ $password ?? '' }}">
                    </div>
                </div>
                <div class="form-row pb-3">
                    <label for="database" class="col-4 col-form-label text-right">@lang('install.database.name')</label>
                    <div class="col-6">
                        <input type="text" class="form-control" name="database" id="database" value="{{ $database ?? 'librenms' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 offset-1">
                        <strong>@lang('install.database.status'):</strong>
                        <span id="database-status" style="vertical-align: middle">
                            @if($status === null)
                                <i class="fa fa-2x fa-question-circle text-muted"></i>
                            @elseif($status)
                                <i class="fa fa-2x fa-check-circle text-success"></i>
                            @else
                                <i class="fa fa-2x fa-times-circle text-danger"></i>
                            @endif
                        </span>
                    </div>
                    <div class="col-7">
                        <button type="submit" class="btn btn-success float-right">@lang('install.database.test')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('#database-form').submit(function (event) {
            event.preventDefault();
            $('#database-status>i').attr('class', 'fa fa-2x fa-spinner fa-spin');
            $('.db-error').remove();

            $.ajax({
                type: 'POST',
                dataType: "json",
                url: $('#database-form').attr('action'),
                data: $('#database-form').serialize(),
                success: function (response) {
                    if (response.result === 'ok') {
                        $('#database-status>i').attr('class', 'fa fa-2x fa-check-circle text-success');
                        $('#install-migrate-button').removeClass('disabled');
                    } else {
                        $('#database-status>i').attr('class', 'fa fa-2x fa-times-circle text-danger')
                        if (response.message) {
                            $('#error-box').append($('<div class="alert alert-danger">' + response.message + '</div>'))
                        }
                    }
                },
            });
        });
    </script>
@endsection
