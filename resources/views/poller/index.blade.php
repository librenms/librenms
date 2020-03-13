@extends('layouts.librenmsv1')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <ul class="nav nav-tabs">
        <li role="presentation" @if( $current_tab == 'poller' ) ' class="active"' @endif>
          <a href="/poller"><i class="fa fa-th-large fa-lg icon-theme" aria-hidden="true"></i>@lang('Poller')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'groups' ) ' class="active"' @endif>
          <a href="/poller/groups"><i class="fa fa-th fa-lg icon-theme" aria-hidden="true"></i>@lang('Groups')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'performance' ) ' class="active"' @endif>
          <a href="/poller/performance"><i class="fa fa-line-chart fa-lg icon-theme" aria-hidden="true"></i>@lang('Performance')</a>
        </li>
        <li role="presentation" @if( $current_tab == 'log' ) ' class="active"' @endif>
          <a href="/poller/log"><i class="fa fa-file-text fa-lg icon-theme" aria-hidden="true"></i>@lang('Log')</a>
        </li>
      </ul>
@endsection
@section('content_footer')
    </div>
  </div>
</div>
@endsection
