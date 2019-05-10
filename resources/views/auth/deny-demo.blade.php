@extends('layouts.librenmsv1')

@section('content')
    <div class="container">
        <div class="row col-md-6 col-md-offset-3">
            <div class="alert alert-danger"><i class="fa fa-fw fa-exclamation-circle" aria-hidden="true"></i>
                @lang('You are logged in as a demo account, this page is not accessible to you')
            </div>
        </div>
    </div>
@endsection
