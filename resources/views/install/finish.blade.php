@extends('layouts.install')

@section('content')
<div class="card mb-2">
  <div class="card-header" data-toggle="collapse" data-target="#env-file-text" aria-expanded="false">
      @lang('install.finish.env_written')
      <i class="fa fa-chevron-up rotate-if-collapsed pull-right"></i>
  </div>
  <div id="env-file-text" class="card-body collapse">
      <pre class="card bg-light p-3">{{ $env }}</pre>
  </div>
</div>
<div class="card mb-2">
    <div class="card-header" data-toggle="collapse" data-target="#config-file-text" aria-expanded="false">
        {{ $config_message }}
        <i class="fa fa-chevron-up rotate-if-collapsed pull-right"></i>
    </div>
    <div id="config-file-text" class="card-body collapse">
        <pre class="card bg-light p-3">{{ $config }}</pre>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <p>@lang('install.finish.not_finished')</p>
            <p>@lang('install.finish.validate', ['validate' => '<a href="' . url('validate') . '">' . __('install.finish.validate_link') . '</a>'])</p>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="alert alert-success">
            <p>@lang('install.finish.thanks')</p>
            <p>@lang('install.finish.statistics', ['about' => '<a href="' . url('about') . '">' . __('install.finish.statistics_link') . '</a>'])</p>
        </div>
    </div>
</div>
@endsection
