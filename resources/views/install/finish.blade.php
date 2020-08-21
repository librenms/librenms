@extends('layouts.install')

@section('content')
<div class="card mb-2">
  <div class="card-header h6" data-toggle="collapse" data-target="#env-file-text" aria-expanded="{{ $success ? 'false' : 'true' }}">
    @if($success)
      <i class="fa fa-lg fa-check-square-o text-success"></i>
    @else
      <i class="fa fa-lg fa-times-rectangle-o text-danger"></i>
    @endif
    {{ $env_message }}
    @if($env)<i class="fa fa-lg fa-chevron-down rotate-if-collapsed pull-right"></i>@endif($env)
  </div>
    @if($env)
    <div id="env-file-text" class="card-body collapse @if(!$success) show @endif">
        <button class="btn btn-primary float-right" onclick="location.reload()">@lang('install.finish.retry')</button>
        <strong>
            @lang('install.finish.env_manual', ['file' => base_path('.env')])
        </strong>
        <div class="text-right mt-3">
            <button
                class="btn btn-sm btn-light text-muted copy-btn"
                data-clipboard-target="#env-content"
                data-toggle="tooltip"
                data-placement="bottom"
                data-trigger="manual"
                data-title="@lang('install.finish.copied')"
            >
                <i class="fa fa-clipboard"></i>
            </button>
        </div>
        <pre id="env-content" class="card bg-light p-3">{{ $env }}</pre>
    </div>
    @endif
</div>
<div class="card mb-2">
    <div class="card-header h6" data-toggle="collapse" data-target="#config-file-text" aria-expanded="false">
        <i class="fa fa-lg fa-check-square-o text-success"></i>
        {{ $config_message }}
        @if($config)<i class="fa fa-lg fa-chevron-down rotate-if-collapsed pull-right"></i>@endif
    </div>
    @if($config)
    <div id="config-file-text" class="card-body collapse">
        <strong>
            @lang('install.finish.config_not_required')
        </strong>
        <div class="text-right mt-3">
            <button
                class="btn btn-sm btn-light text-muted copy-btn"
                data-clipboard-target="#config-content"
                data-toggle="tooltip"
                data-placement="bottom"
                data-trigger="manual"
                data-title="@lang('install.finish.copied')"
            >
                <i class="fa fa-clipboard"></i>
            </button>
        </div>
        <pre id="config-content" class="card bg-light p-3">{{ $config }}</pre>
    </div>
    @endif
</div>
@if($success)
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
@endif
@endsection

@section('scripts')
<script>
    var clipboard = new ClipboardJS('.copy-btn');
    clipboard.on('success', function(e) {
        $(e.trigger).tooltip('show');
        setTimeout(() => $(e.trigger).tooltip('hide'), 2000);

        e.clearSelection();
    });

    clipboard.on('error', function(e) {
        $(e.trigger).data('title', '@lang('install.finish.manual_copy')').tooltip('show');
        setTimeout(() => $(e.trigger).tooltip('hide'), 2000);
    });
</script>
@endsection
