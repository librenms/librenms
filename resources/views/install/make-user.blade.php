@extends('layouts.install')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <form class="form-horizontal" role="form" method="post" action="{{ route('install.user-create') }}">
                @csrf
                <div class="form-group">
                    <label for="username" class="col-sm-4 control-label">@lang('install.user.username')</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="username" id="username" value="{{ old('username') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-sm-4 control-label">@lang('install.user.password')</label>
                    <div class="col-sm-6">
                        <input type="password" class="form-control" name="password" id="password" value="{{ old('password') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-4 control-label">@lang('install.user.email')</label>
                    <div class="col-sm-6">
                        <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-success pull-right">@lang('install.user.button')</button>
            </form>
        </div>
    </div>
@endsection
