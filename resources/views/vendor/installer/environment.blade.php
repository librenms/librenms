@extends('vendor.installer.layouts.master')

@section('template_title')
    {{ trans('installer_messages.environment.menu.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-cog fa-fw" aria-hidden="true"></i>
    {!! trans('installer_messages.environment.menu.title') !!}
@endsection

@section('container')

    <p class="text-center">
        {!! trans('installer_messages.environment.menu.desc') !!}
    </p>
    <div class="buttons">
        <a href="{{ route('LaravelInstaller::environmentWizard') }}" class="button button-wizard">
            <i class="fa fa-sliders fa-fw" aria-hidden="true"></i> {{ trans('installer_messages.environment.menu.wizard-button') }}
        </a>
        <a href="{{ route('LaravelInstaller::environmentClassic') }}" class="button button-classic">
            <i class="fa fa-code fa-fw" aria-hidden="true"></i> {{ trans('installer_messages.environment.menu.classic-button') }}
        </a>
    </div>

@endsection
