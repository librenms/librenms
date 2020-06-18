@extends('layouts.install')

@section('title', trans('install.database.title'))

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-2">
                <div id="db-form-header"
                     class="card-header h6"
                     data-toggle="collapse"
                     href="#db-form-container"
                     aria-expanded="@if($valid_credentials) false @else true @endif"
                >
                    <span id="database-status">
                       @if($valid_credentials === null)
                          <i class="fa fa-lg fa-question-circle text-muted"></i>
                       @elseif($valid_credentials)
                          <i class="fa fa-lg fa-check-circle text-success"></i>
                       @else
                          <i class="fa fa-lg fa-times-circle text-danger"></i>
                       @endif
                    </span>
                    @lang('install.database.credentials')
                    <i class="fa fa-lg fa-chevron-down rotate-if-collapsed fa-pull-right"></i>
                </div>
                <div id="db-form-container" class="card-body collapse @if(!$valid_credentials) show @endif">
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
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary float-right">@lang('install.database.test')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row" @if(!$valid_credentials) style="display: none" @endif>
        <div class="col-12">
            <div class="card">
                <div id="db-form-header"
                     class="card-header h6"
                     data-toggle="collapse"
                     href="#db-migration-container"
                     aria-expanded="@if($migrated) false @else true @endif"
                >
                  @if($migrated === null)
                      <i class="fa fa-lg fa-question-circle text-muted"></i>
                  @elseif($migrated)
                      <i class="fa fa-lg fa-check-circle text-success"></i>
                  @else
                      <i class="fa fa-lg fa-times-circle text-danger"></i>
                  @endif
                  @lang('install.migrate.migrate')
                  <i class="fa fa-lg fa-chevron-down rotate-if-collapsed fa-pull-right"></i>
                </div>
              <div id="db-migration-container" class="card-body collapse @if(!$migrated) show @endif">
                  <div class="mb-2 text-right">
                      <button type="button" id="retry-btn" onClick="alert('do something')" class="btn btn-primary">
                          @lang('install.migrate.retry')
                      </button>
                      <button type="button" class="btn btn-primary">
                          @lang('install.migrate.migrate')
                      </button>
                  </div>
                  <div class="alert alert-warning">@lang('install.migrate.building_interrupt')</div>
                  <textarea readonly id="db-update" class="form-control" rows="20" placeholder="@lang('install.migrate.wait')"></textarea>
              </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('#database-form').submit(function (event) {
            event.preventDefault();
            $('#database-status>i').attr('class', 'fa fa-lg fa-spinner fa-spin');
            $('#error-box').empty();

            $.ajax({
                type: 'POST',
                dataType: "json",
                url: $('#database-form').attr('action'),
                data: $('#database-form').serialize(),
                success: function (response) {
                    if (response.result === 'ok') {
                        $('#database-status>i').attr('class', 'fa fa-lg fa-check-circle text-success');
                        $('#migration-output').show();
                        $('#db-form-container').collapse('hide')
                    } else {
                        $('#database-status>i').attr('class', 'fa fa-lg fa-times-circle text-danger')
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
        #db-update {
            resize: vertical;
        }

        #retry-btn {
            display: none;
        }
    </style>
@endsection
