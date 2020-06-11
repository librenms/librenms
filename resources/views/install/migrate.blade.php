@extends('layouts.install')

@section('title', trans('install.migrate.title'))

@section('content')
    <div class="row">
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
    <script type="text/javascript">
        var output = document.getElementById("db-update");
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "{{ route('install.action.migrate') }}", true);
        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
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
    </style>
@endsection
