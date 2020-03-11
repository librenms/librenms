@extends('layouts.librenmsv1')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <ul class="nav nav-tabs">
        @foreach($navbar as $tab)
        <li role="presentation" @if( $current_tab == $tab['taburl'] ) ' class="active"' @endif>
          <a href="/poller-groups?tab={{ $tab['taburl'] }}"><i class="fa {{ $tab['icon'] }} fa-lg icon-theme" aria-hidden="true"></i> {{ $tab['name'] }}</a>
        </li>
        @endforeach
      </ul>
@endsection
@section('content_footer')
    </div>
  </div>
</div>
@endsection
