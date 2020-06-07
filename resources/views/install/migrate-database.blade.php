@extends('layouts.install')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <h5 class="text-center">Importing MySQL DB - Do not close this page or interrupt the import</h5>
            <textarea readonly id="db-update" class="form-control" rows="20" placeholder="Please Wait..." style="resize:vertical;"></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            If you don't see any errors or messages above then the database setup has been successful.<br />
            <form class="form-horizontal" role="form" method="post">
                @csrf
                <input type="button" id="retry-btn" value="Retry" onClick="window.location.reload()" style="display: none;" class="btn btn-success">
                <button type="submit" id="add-user-btn" class="btn btn-success pull-right" disabled>Goto Add User</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        var output = document.getElementById("db-update");
        xhr = new XMLHttpRequest();
        xhr.open("GET", "{{ route('install.action.migrate') }}", true);
        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
        xhr.withCredentials = true;
        xhr.onprogress = function (e) {
            output.innerHTML = e.currentTarget.responseText;
            output.scrollTop = output.scrollHeight - output.clientHeight; // scrolls the output area
            if (output.innerHTML.indexOf('Error!') !== -1) {
                // if error word in output, show the retry button
                $("#retry-btn").css("display", "");
            }
        };
        xhr.timeout = 90000; // if no response for 90s, allow the user to retry
        xhr.ontimeout = function (e) {
            $("#retry-btn").css("display", "");
        };
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    $('#install-user-button').removeClass('disabled');
                }
            }
        };
        xhr.send();
    </script>
@endsection
