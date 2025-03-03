@extends('layouts.install')

@section('content')
    <div class="card mb-2">
        <div class="card-header h6">
            {{ __('install.finish.settings') }}
        </div>
        <div class="card-body">
            <form id="settings">
                @if($can_update)
                <div class="mb-3">
                    <span>{{ __('settings.settings.update_channel.description') }}</span>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="update_channel_daily" name="update_channel" class="custom-control-input" value="master" @config('update_channel', 'master') checked @endconfig>
                        <label class="custom-control-label" for="update_channel_daily">{{ __('settings.settings.update_channel.options.master') }}</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="update_channel_monthly" name="update_channel" class="custom-control-input" value="release" @config('update_channel', 'release') checked @endconfig>
                        <label class="custom-control-label" for="update_channel_monthly">{{ __('settings.settings.update_channel.options.release') }}</label>
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <span>{{ __('settings.settings.site_style.description') }}</span>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="site_style_light" name="site_style" class="custom-control-input" value="light" @config('site_style', 'light') checked @endconfig>
                        <label class="custom-control-label" for="site_style_light">{{ __('settings.settings.site_style.options.light') }}</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="site_style_dark" name="site_style" class="custom-control-input" value="dark" @config('site_style', 'dark') checked @endconfig>
                        <label class="custom-control-label" for="site_style_dark">{{ __('settings.settings.site_style.options.dark') }}</label>
                    </div>
                </div>

                <div class="custom-control custom-checkbox mb-3">
                    <input type="checkbox" class="custom-control-input" id="usage_reporting" name="usage_reporting" @config('reporting.usage') checked @endconfig>
                    <label class="custom-control-label" for="usage_reporting"><a target="_blank" href="https://stats.librenms.org/">{{ __('settings.settings.reporting.usage.description') }}</a></label>
                </div>

                <div class="custom-control custom-checkbox mb-3">
                    <input type="checkbox" class="custom-control-input" id="error_reporting" name="error_reporting" @config('reporting.error') checked @endconfig>
                    <label class="custom-control-label" for="error_reporting">{{ __('settings.settings.reporting.error.description') }}</label>
                </div>

                <div>
                    <button type="button" class="btn btn-primary finalize-buttons">{{ __('install.finish.finish') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div id="finished" class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card mb-2">
                        <div id="env-header" class="card-header h6" data-toggle="collapse" data-target="#env-file-text" aria-expanded="false">
                            <i id="env-icon" class="fa-solid fa-lg"></i>
                            <span id="env-message"></span>
                            <span id="env-chevron" class="float-right"><i class="fa-solid fa-lg fa-chevron-down rotate-if-collapsed"></i></span>
                        </div>
                        <div id="env-file-text" class="card-body collapse">
                            <button class="btn btn-primary float-right finalize-buttons">{{ __('install.finish.retry') }}</button>
                            <strong>
                                {{ __('install.finish.env_manual', ['file' => base_path('.env')]) }}
                            </strong>
                            <div class="text-right mt-3">
                                <button
                                        class="btn btn-sm btn-light text-muted copy-btn"
                                        data-clipboard-target="#env-content"
                                        data-toggle="tooltip"
                                        data-placement="bottom"
                                        data-trigger="manual"
                                        data-title="{{ __('install.finish.copied') }}"
                                >
                                    <i class="fa-solid fa-clipboard"></i>
                                </button>
                            </div>
                            <pre id="env-content" class="card bg-light p-3"></pre>
                        </div>
                    </div>
                    <div class="card mb-2">
                        <div class="card-header h6" data-toggle="collapse" data-target="#config-file-text" aria-expanded="false">
                            <i id="config-icon" class="fa-solid fa-lg"></i>
                            <span id="config-message"></span>
                            <span id="config-chevron" class="float-right"><i class="fa-solid fa-lg fa-chevron-down rotate-if-collapsed"></i></span>
                        </div>
                        <div id="config-file-text" class="card-body collapse">
                            <strong>
                                {{ __('install.finish.config_not_required') }}
                            </strong>
                            <div class="text-right mt-3">
                                <button
                                        class="btn btn-sm btn-light text-muted copy-btn"
                                        data-clipboard-target="#config-content"
                                        data-toggle="tooltip"
                                        data-placement="bottom"
                                        data-trigger="manual"
                                        data-title="{{ __('install.finish.copied') }}"
                                >
                                    <i class="fa-solid fa-clipboard"></i>
                                </button>
                            </div>
                            <pre id="config-content" class="card bg-light p-3"></pre>
                        </div>
                    </div>
                    <div class="row" id="success-message">
                        <div class="col-12">
                            <div class="alert alert-success">
                                <i class="fa-solid fa-2x fa-heart" style="color: #ff4033;"></i>
                                <span class="h4 align-text-bottom">{{ __('install.finish.thanks') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="modal-retry" type="button" class="btn btn-primary finalize-buttons">{{ __('install.finish.retry') }}</button>
                    <div id="modal-finished">
                        <a href="{{ route('home') }}">
                            <button type="button" class="btn btn-secondary">{{ __('install.finish.dashboard') }}</button>
                        </a>
                        <a href="{{ url('validate') }}">
                            <button type="button" class="btn btn-primary">{{ __('install.finish.validate_button') }}</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('.finalize-buttons').on('click', function (e) {
            var data = $('#settings').serializeArray();
            $.ajax('{{ route('install.finish.save') }}', {
                method: 'post',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                data: data
            }).done((result) => {
                if (result.success) {
                    $('#env-header').attr('aria-expanded', 'false');
                    $('#env-file-text').addClass('show');
                    $('#success-message').show();
                    $('.modal-title').text('{{ __('install.finish.success') }}')
                    $('#modal-retry').hide();
                    $('#modal-finished').show();
                } else {
                    $('#env-header').attr('aria-expanded', 'true');
                    $('#env-file-text').removeClass('show');
                    $('#success-message').hide();
                    $('.modal-title').text('{{ __('install.finish.failed') }}')
                    $('#modal-retry').show();
                    $('#modal-finished').hide();
                }

                $('#env-message').text(result.env_message);
                $('#env-content').text(result.env);
                if (result.env) {
                    $('#env-chevron').show();
                    $('#env-file-text').removeAttr('style').addClass('show');
                    $('#env-icon').removeClass(['fa-square-check', 'text-success']).addClass(['fa-rectangle-xmark', 'text-danger']);
                } else {
                    $('#env-file-text').hide();
                    $('#env-chevron').hide();
                    $('#env-icon').addClass(['fa-square-check', 'text-success']).removeClass(['fa-rectangle-xmark', 'text-danger']);
                }

                $('#config-message').text(result.config_message);
                $('#config-content').text(result.config);
                if (result.config) {
                    $('#config-file-text').removeAttr('style');
                    $('#config-chevron').show();
                    $('#config-icon').removeClass(['fa-square-check', 'text-success']).addClass(['fa-rectangle-xmark', 'text-danger']);
                } else {
                    $('#config-file-text').hide();
                    $('#config-chevron').hide();
                    $('#config-icon').addClass(['fa-square-check', 'text-success']).removeClass(['fa-rectangle-xmark', 'text-danger']);
                }

                $('#finished').modal('show')
            }).fail(function (output) {
                location.reload();
            });
        });

        var clipboard = new ClipboardJS('.copy-btn');
        clipboard.on('success', function (e) {
            $(e.trigger).tooltip('show');
            setTimeout(() => $(e.trigger).tooltip('hide'), 2000);

            e.clearSelection();
        });

        clipboard.on('error', function (e) {
            $(e.trigger).data('title', '{{ __('install.finish.manual_copy') }}').tooltip('show');
            setTimeout(() => $(e.trigger).tooltip('hide'), 2000);
        });
    </script>
@endsection
