@extends('layouts.librenmsv1')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <ul class="nav nav-tabs">
        <li role="presentation" @if( $current_tab == 'services' ) class="active" @endif>
          <a href="{{ route('services.index') }}"><i class="fa fa-cogs icon-theme fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> @lang('All Services')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'errors' ) class="active" @endif>
          <a href="{{ route('services.errors') }}"><i class="fa fa-exclamation-circle fa-col-danger fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> @lang('Errors')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'warnings' ) class="active" @endif>
          <a href="{{ route('services.warnings') }}"><i class="fa fa-exclamation-triangle fa-col-warning fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> @lang('Warnings')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'ignored' ) class="active" @endif>
          <a href="{{ route('services.ignored') }}"><i class="fa fa-eye-slash fa-col-info fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> @lang('Ignored')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'disabled' ) class="active" @endif>
          <a href="{{ route('services.disabled') }}"><i class="fa fa-ban icon-theme fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> @lang('Disabled')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'maintenance' ) class="active" @endif>
          <a href="{{ route('services.maintenance') }}"><i class="fa fa-calendar fa-col-maintenance fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> @lang('Maintenance')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'unknown' ) class="active" @endif>
            <a href="{{ route('services.unknown') }}"><i class="fa fa-question-circle icon-theme fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> @lang('Unknown')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'log' ) class="active" @endif>
          <a href="{{ route('services.log') }}"><i class="fa fa-stack-overflow fa-lg icon-theme" aria-hidden="true"></i> @lang('Logs')</a>
        </li>
        <li role="presentation" class="btn btn-primary">
            <a href="{{ route('services.create') }}"><i class="fa fa-plus-square fa-col-info fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> @lang('Add Service')</a>
        </li>
      </ul>
      <br />
@endsection

@section('content_footer')
    </div>
  </div>
</div>
@endsection
