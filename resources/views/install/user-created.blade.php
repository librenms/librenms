@extends('layouts.install')

@section('content')
    <div class="row">
        <div class="col-12 text-center" style="padding: 60px">
            <h4>
                @lang('install.user.created'):
                <i class="fa fa-2x fa-user-circle" style="vertical-align: middle"></i>
                <strong>{{ $user->username }}</strong>
            </h4>
        </div>
    </div>
@endsection
