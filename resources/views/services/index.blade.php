@extends('layouts.librenmsv1')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <ul class="nav nav-tabs">
        <li role="presentation" @if( $current_tab == 'services' ) class="active" @endif>
          <a href="{{ route('services.index') }}"><i class="fa fa-th-large fa-lg icon-theme" aria-hidden="true"></i> @lang('All Services')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'error' ) class="active" @endif>
          <a href="{{ route('services.index') }}"><i class="fa fa-th fa-lg icon-theme" aria-hidden="true"></i> @lang('Errors')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'warning' ) class="active" @endif>
          <a href="{{ route('services.index') }}"><i class="fa fa-gears fa-lg icon-theme" aria-hidden="true"></i> @lang('Warnings')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'ignored' ) class="active" @endif>
          <a href="{{ route('services.index') }}"><i class="fa fa-line-chart fa-lg icon-theme" aria-hidden="true"></i> @lang('Ignored')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'disabled' ) class="active" @endif>
          <a href="{{ route('services.index') }}"><i class="fa fa-line-chart fa-lg icon-theme" aria-hidden="true"></i> @lang('Disabled')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'maintenance' ) class="active" @endif>
          <a href="{{ route('services.index') }}"><i class="fa fa-line-chart fa-lg icon-theme" aria-hidden="true"></i> @lang('Maintenance')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'log' ) class="active" @endif>
          <a href="{{ route('services.index') }}"><i class="fa fa-file-text fa-lg icon-theme" aria-hidden="true"></i> @lang('Log')</a>
        </li>
      </ul>
      <br />
@endsection

@section('content_footer')
    </div>
  </div>
</div>
@endsection
