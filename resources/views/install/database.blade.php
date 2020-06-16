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
    <div id="migration-output" class="row">
        <div class="col-12">
            <label for="db-update">@lang('install.migrate.building')<br />@lang('install.migrate.building_interrupt')</label>
            <textarea readonly id="db-update" class="form-control" rows="20" placeholder="@lang('install.migrate.wait')"></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <button type="button" id="retry-btn" onClick="window.location.reload()" class="btn btn-success pull-right">
                @lang('install.migrate.retry')
            </button>
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
                    } else {
                        $('#database-status>i').attr('class', 'fa fa-2x fa-times-circle text-danger')
                        if (response.message) {
                            $('#error-box').append($('<div class="alert alert-danger">' + response.message + '</div>'))
                        }
                    }
                    checkStepStatus();
                },
            });
        });

        function run_migrations() {
            var output = document.getElementById("db-update");
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "{{ route('install.action.migrate') }}", true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.withCredentials = true;
            xhr.onprogress = function (e) {
                output.innerHTML = e.currentTarget.responseText;
                output.scrollTop = output.scrollHeight - output.clientHeight; // scrolls the output area
                if (output.innerHTML.indexOf('Error!') !== -1) {
                    // if error word in output, show the retry button
                    $("#retry-btn").css("display", "");
                    $('#error-box').append($('<div class="alert alert-danger">@lang('install.migrate.error')</div>'))
                }
            };
            xhr.timeout = 240000; // if no response for 4m, allow the user to retry
            xhr.ontimeout = function (e) {
                $("#retry-btn").css("display", "");
                $('#error-box').append($('<div class="alert alert-danger">@lang('install.migrate.timeout')</div>'))
            };
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                    checkStepStatus();
                }
            };
            xhr.send();
        }
    </script>
@endsection

@section('style')
    <style type="text/css">
        label[for=db-update] {
            font-size: large;
        }
        #db-update {
            resize:vertical;
        }
        #retry-btn {
            display: none;
        }
        #migration-output {
            display: none;
        }
    </style>
@endsection
