@extends('layouts.librenmsv1')

@section('title', __('dashboard.noc.cleanup_title'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                <strong>{{ __('dashboard.noc.cleanup_title') }}</strong><br>
                {{ __('dashboard.noc.cleanup_message') }}
            </div>

            <p>{{ __('dashboard.noc.cleanup_affected_playlists') }}</p>
            <ul>
                @foreach($playlists_with_missing as $playlist)
                    <li>{{ $playlist['name'] }}</li>
                @endforeach
            </ul>

            <p>{{ __('dashboard.noc.cleanup_missing_ids') }}</p>
            <ul>
                @foreach($missing_ids as $missing_id)
                    <li>#{{ $missing_id }}</li>
                @endforeach
            </ul>

            <form method="POST" action="{{ route('noc.cleanup.all') }}" style="display: inline-block;">
                @csrf
                <button type="submit" class="btn btn-danger">{{ __('dashboard.noc.cleanup_confirm') }}</button>
            </form>
            <a class="btn btn-default" href="{{ route('noc.playlists') }}">{{ __('dashboard.noc.cleanup_cancel') }}</a>
        </div>
    </div>
</div>
@endsection
