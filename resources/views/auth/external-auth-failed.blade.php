@extends('layouts.librenmsv1')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3>
            <h1>External Authentication Failed.</h1>
            <p>Please close your browser window to try again or contact your administrator.</p>
            @if($message)
                <br />
                <p class="text-danger">{{ $message }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
