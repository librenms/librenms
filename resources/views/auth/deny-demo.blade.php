@extends('layouts.librenmsv1')

@section('content')
    <div class="container">
        <div class="row">
            <div class="alert alert-danger col-md-6 col-md-offset-3"><i class="fa fa-fw fa-exclamation-circle" aria-hidden="true"></i>
                {{ __('You are logged in as a demo account, this page is not accessible to you') }}
            </div>
        </div>
    </div>
@endsection
