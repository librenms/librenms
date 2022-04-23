@extends('layouts.install')

@section('content')
    <div class="row">
        <div class="col-12">
            <form class="form-horizontal" role="form" method="post" action="{{ route('install.action.user') }}">
                @csrf
                <div class="form-row pb-3">
                    <label for="username" class="col-sm-4 col-form-label text-right">{{ __('install.user.username') }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" id="username" value="{{ old('username') }}">
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row pb-3">
                    <label for="password" class="col-sm-4 col-form-label text-right">{{ __('install.user.password') }}</label>
                    <div class="col-sm-6">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" value="{{ old('password') }}">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row pb-3">
                    <label for="email" class="col-sm-4 col-form-label text-right">{{ __('install.user.email') }}</label>
                    <div class="col-sm-6">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary float-right">{{ __('install.user.button') }}</button>
            </form>
        </div>
    </div>
@endsection
