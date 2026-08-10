@extends('layouts.librenmsv1')

@section('title', __('dashboard.noc.cleanup_title'))

@section('content')
<div class="container-fluid">
    <div class="row tw:mt-4">
        <div class="col-md-12">
            <x-panel title="{{ __('dashboard.noc.cleanup_title') }}">
                <div class="alert alert-warning tw:mb-4">
                    <strong>{{ __('dashboard.noc.cleanup_title') }}</strong><br>
                    {{ __('dashboard.noc.cleanup_message') }}
                </div>

                <div class="tw:space-y-4">
                    <div>
                        <p class="tw:mb-2">
                            {{ __('dashboard.noc.cleanup_affected_playlists') }}
                        </p>
                        <ul class="tw:list-disc tw:pl-6 tw:space-y-1">
                            @foreach ($playlists_with_missing as $playlist)
                                <li>{{ $playlist['name'] }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <p class="tw:mb-2">
                            {{ __('dashboard.noc.cleanup_missing_ids') }}
                        </p>
                        <ul class="tw:list-disc tw:pl-6 tw:space-y-1">
                            @foreach ($missing_ids as $missing_id)
                                <li>#{{ $missing_id }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                        <form method="POST" action="{{ route('noc.cleanup.all') }}" class="tw:inline-block">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                {{ __('dashboard.noc.cleanup_confirm') }}
                            </button>
                        </form>
                        <a class="btn btn-default" href="{{ route('noc.playlists') }}">
                            {{ __('dashboard.noc.cleanup_cancel') }}
                        </a>
                    </div>
                </div>
            </x-panel>
        </div>
    </div>
</div>
@endsection
