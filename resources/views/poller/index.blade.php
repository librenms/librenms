@extends('layouts.librenmsv1')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <ul class="nav nav-tabs">
        <li role="presentation" @if( $current_tab == 'poller' ) class="active" @endif>
          <a href="{{ route('poller.index') }}"><i class="fa fa-th-large fa-lg icon-theme" aria-hidden="true"></i> {{ __('Poller') }}</a>
        </li>
        <li role="presentation" @if( $current_tab == 'groups' ) class="active" @endif>
          <a href="{{ route('poller.groups') }}"><i class="fa fa-th fa-lg icon-theme" aria-hidden="true"></i> {{ __('Groups') }}</a>
        </li>
          @if(\App\Models\PollerCluster::exists())
            <li role="presentation" @if( $current_tab == 'settings' ) class="active" @endif>
              <a href="{{ route('poller.settings') }}"><i class="fa fa-gears fa-lg icon-theme" aria-hidden="true"></i> {{ __('Settings') }}</a>
            </li>
          @endif
        <li role="presentation" @if( $current_tab == 'performance' ) class="active" @endif>
          <a href="{{ route('poller.performance') }}"><i class="fa fa-line-chart fa-lg icon-theme" aria-hidden="true"></i> {{ __('Performance') }}</a>
        </li>
        <li role="presentation" @if( $current_tab == 'log' ) class="active" @endif>
          <a href="{{ route('poller.log') }}"><i class="fa fa-file-text fa-lg icon-theme" aria-hidden="true"></i> {{ __('Log') }}</a>
        </li>
      </ul>
      <br />
@endsection

@section('content_footer')
    </div>
  </div>
</div>
@endsection
