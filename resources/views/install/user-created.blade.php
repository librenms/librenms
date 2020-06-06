@extends('layouts.install')

@section('content')
    <div class="row">
        <div class="col-xs-12 text-center">
            <h3>
                @lang('install.user.created'):
                <i class="fa fa-2x fa-user-circle" style="vertical-align: middle"></i>
                <strong>{{ $user->username }}</strong>
            </h3>
        </div>
    </div>
@endsection
