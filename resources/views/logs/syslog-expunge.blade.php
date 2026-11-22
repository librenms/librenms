@extends('layouts.librenmsv1')

@section('title', __('Clear Syslog'))

@section('content')
    <div class="container-fluid">
        <x-panel :title="__('Clear Syslog')">
            <p>{{ __('This permanently deletes all syslog entries for all devices.') }}</p>
            <form action="{{ route('syslog.expunge') }}" method="post" class="tw:flex tw:items-center tw:gap-2">
                @csrf
                <button type="submit" class="btn btn-danger">{{ __('Clear Syslog') }}</button>
                <a href="{{ route('syslog') }}" class="btn btn-default">{{ __('Cancel') }}</a>
            </form>
        </x-panel>
    </div>
@endsection
