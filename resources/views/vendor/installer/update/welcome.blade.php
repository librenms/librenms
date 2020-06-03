@extends('vendor.installer.layouts.master-update')

@section('title', trans('installer_messages.updater.welcome.title'))
@section('container')
    <p class="paragraph text-center">
    	{{ trans('installer_messages.updater.welcome.message') }}
    </p>
    <div class="buttons">
        <a href="{{ route('LaravelUpdater::overview') }}" class="button">{{ trans('installer_messages.next') }}</a>
    </div>
@stop
