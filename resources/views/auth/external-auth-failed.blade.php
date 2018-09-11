@extends('layouts.librenmsv1')

@section('content')
    <div class="row">
        <div class="col-md-offset-4 col-md-4">
            <h1>External Authentication Failed.</h1>
            <p>Please close your browser window to try again or contact your administrator.</p>
            @if($message)
                <br />
                <p class="text-danger">{{ $message }}</p>
            @endif
        </div>
    </div>
@endsection
