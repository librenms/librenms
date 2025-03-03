@extends('layouts.librenmsv1')

@section('title', __('preferences.title'))

@section('content')
<div class="container">
    <row>
        <legend>{{ __('User Preferences') }}</legend>
    </row>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-panel title="{{ __('Push Notifications') }}">
        <x-notification-subscription-status></x-notification-subscription-status>
    </x-panel>

    @if($can_change_password)
    <x-panel title="{{ __('Change Password') }}">
        <form method="POST" action="{{ route('users.update', [$user->user_id]) }}" class="form-horizontal" role="form">
            <input type="hidden" name="_method" value="PATCH">
            @csrf
            <div class="form-group">
                <label for="old_password" class="col-sm-4 control-label">{{ __('Current Password') }}</label>
                <div class="col-sm-4">
                    <input type="password" name="old_password" autocomplete="off" class="form-control input-sm">
                </div>
            </div>
            <div class="form-group">
                <label for="new_password" class="col-sm-4 control-label">{{ __('New Password') }}</label>
                <div class="col-sm-4">
                    <input type="password" name="new_password" autocomplete="off" class="form-control input-sm">
                </div>
            </div>
            <div class="form-group">
                <label for="new_password_confirmation" class="col-sm-4 control-label">{{ __('Verify New Password') }}</label>
                <div class="col-sm-4">
                    <input type="password" name="new_password_confirmation" autocomplete="off" class="form-control input-sm">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-4 col-sm-offset-4">
                    <button type="submit" class="btn btn-default">{{ __('Change Password') }}</button>
                </div>
            </div>
        </form>
    </x-panel>
    @endif

    <x-panel title="{{ __('preferences.title') }}">
        <form class="form-horizontal" role="form">
            @csrf
            <div class="form-group">
                <label for="dashboard" class="col-sm-4 control-label">{{ __('Dashboard') }}</label>
                <div class="col-sm-4">
                    <select class="form-control ajax-select" name="dashboard" data-pref="dashboard" data-previous="{{ $default_dashboard }}">
                        @foreach($dashboards as $dash)
                            <option value="{{ $dash->dashboard_id }}" @if($dash->dashboard_id == $default_dashboard) selected @endif>{{ $dash->user ? $dash->user->username : __('<deleted>') }}:{{ $dash->dashboard_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="site_style" class="col-sm-4 control-label">{{ __('CSS Style') }}</label>
                <div class="col-sm-4">
                    <select class="form-control ajax-select" name="site_style" data-pref="site_style" data-previous="{{ $site_style }}">
                        <option value="default">{{ __('Default') }} ({{ $site_style_default }})</option>
                        @foreach($site_styles as $style => $descr)
                            <option value="{{ $style }}" @if($style == $site_style) selected @endif>{{ $descr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="locale" class="col-sm-4 control-label">{{ __('Language') }}</label>
                <div class="col-sm-4">
                    <select class="form-control ajax-select" name="locale" data-pref="locale" data-previous="{{ $locale }}">
                        <option value="default">{{ __('Default') }} ({{ $locale_default }})</option>
                        @foreach($locales as $lang => $descr)
                            <option value="{{ $lang }}" @if($lang == $locale) selected @endif>{{ $descr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <small>* {{ __('Translation not fully supported') }}</small>
                </div>
            </div>
            <div class="form-group">
                <label for="timezone" class="col-sm-4 control-label">{{ __('Timezone') }}</label>
                <div class="col-sm-4">
                    <select class="form-control ajax-select" name="timezone" data-pref="timezone" data-previous="{{ $timezone }}">
                        <option value="default">Browser Timezone</option>
                        @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" @if($timezone == $tz) selected @endif>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="notetodevice" class="col-sm-4 control-label">{{ __('Add schedule notes to devices notes') }}</label>
                <div class="col-sm-4">
                    <input id="notetodevice" type="checkbox" name="notetodevice" @if($note_to_device) checked @endif>
                </div>
            </div>
            <div class="form-group">
                <label for="global_search_ctrlf_focus" class="col-sm-4 control-label">{{ __('Ctrl-F to focus the global search bar') }}</label>
                <div class="col-sm-4">
                    <input id="global_search_ctrlf_focus" type="checkbox" name="global_search_ctrlf_focus" @if($global_search_ctrlf_focus) checked @endif>
                </div>
            </div>
        </form>
    </x-panel>

    @config('auth.socialite.configs')
    <x-panel title="{{ __('OAuth/SAML Authentication') }}">
        @foreach (\LibreNMS\Config::get('auth.socialite.configs', []) as $provider => $config)
        <form role="form" action="{{ route('socialite.redirect', $provider) }}" method="post">
            {{ csrf_field() }}
            <button type="submit" id="login" class="btn btn-success btn-block">
                <i class="fab fa-btn fa-{{ $provider }}"></i> {{ __('Register with') }} {{ ucfirst($provider) }}
            </button>
        </form>
        @endforeach
    </x-panel>
    @endconfig

    @config('twofactor')
    <x-panel title="{{ __('Two-Factor Authentication') }}">
        @if($twofactor)
            <div id="twofactorqrcontainer">
                <div id="twofactorqr"></div>
                <script>$("#twofactorqr").qrcode({"text": "{{ $twofactor_uri }}"});</script>
                <button class="btn btn-default" onclick="$('#twofactorkeycontainer').show(); $('#twofactorqrcontainer').hide();">{{ __('Manual') }}</button>
            </div>
            <div id="twofactorkeycontainer">
                <form id="twofactorkey" class="form-horizontal" role="form">
                    @csrf
                    <div class="form-group">
                        <label for="twofactorkey" class="col-sm-4 control-label">{{ __('Secret Key') }}</label>
                        <div class="col-sm-4">
                            <input type="text" name="twofactorkey" autocomplete="off" disabled class="form-control input-sm" value="{{ $twofactor['key'] }}" />
                        </div>
                    </div>
                    @if($twofactor['counter'] !== false)
                    <div class="form-group">
                        <label for="twofactorcounter" class="col-sm-4 control-label">{{ __('Counter') }}</label>
                        <div class="col-sm-4">
                            <input type="text" name="twofactorcounter" autocomplete="off" disabled class="form-control input-sm" value="{{ $twofactor['counter'] }}" />
                        </div>
                    </div>
                    @endif
                </form>
                <button class="btn btn-default" onclick="$('#twofactorkeycontainer').hide(); $('#twofactorqrcontainer').show();">{{ __('QR') }}</button>
            </div>
                <br/>
                <form method="post" class="form-horizontal" role="form" action="{{ route('2fa.remove') }}">
                    @csrf
                    <button class="btn btn-danger" type="submit">{{ __('Disable TwoFactor') }}</button>
                </form>
        @else
            <form method="post" class="form-horizontal" role="form" action="{{ route('2fa.add') }}">
                @csrf
                <div class="form-group">
                    <label for="twofactortype" class="col-sm-4 control-label">{{ __('TwoFactor Type') }}</label>
                    <div class="col-sm-4">
                        <select name="twofactortype" class="select form-control">
                            <option value="time">{{ __('Time Based (TOTP)') }}</option>
                            <option value="counter">{{ __('Counter Based (HOTP)') }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-4">
                        <button class="btn btn-default" type="submit" id="twofactor-generate">{{ __('Generate TwoFactor Secret Key') }}</button>
                    </div>
                </div>
            </form>
        @endif
    </x-panel>
    @endconfig

    <x-panel title="{{ __('Roles') }}">
        @forelse(auth()->user()->roles->pluck('title') as $role)
            <span class="label label-info tw-mr-1">{{ $role }}</span>
        @empty
            <strong class="red">{{ __('No roles!') }}</strong>
        @endforelse
    </x-panel>

    <x-panel title="{{ __('Device Permissions') }}">
        @if(auth()->user()->hasGlobalAdmin())
            <strong class="blue">{{ __('Global Administrative Access') }}</strong>
        @elseif(auth()->user()->hasGlobalRead())
            <strong class="green">{{ __('Global Viewing Access') }}</strong>
        @else
            @forelse($devices as $device)
                <x-device-link :device="$device" /><br />
            @empty
                <strong class="red">{{ __('No access!') }}</strong>
            @endforelse
        @endif
    </x-panel>
</div>
@endsection

@section('javascript')
    <script src="{{ asset('js/jquery-qrcode.min.js') }}"></script>
@endsection

@section('scripts')
    <script>
        $("[name='notetodevice']")
            .bootstrapSwitch('offColor', 'danger')
            .on('switchChange.bootstrapSwitch', function (e, state) {
                var $this = $(this);
                $.ajax({
                    url: '{{ route('preferences.store') }}',
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        pref: 'add_schedule_note_to_device',
                        value: state ? 1 : 0
                    },
                    success: function () {
                        $this.closest('.form-group').addClass('has-success');
                        setTimeout(function () {
                            $this.closest('.form-group').removeClass('has-success');
                        }, 2000);
                    },
                    error: function () {
                        $this.bootstrapSwitch('toggleState', true);
                        $this.closest('.form-group').addClass('has-error');
                        setTimeout(function(){
                            $this.closest('.form-group').removeClass('has-error');
                        }, 2000);
                    }
                });
            });

        $("[name='global_search_ctrlf_focus']")
            .bootstrapSwitch('offColor', 'danger')
            .on('switchChange.bootstrapSwitch', function (e, state) {
                var $this = $(this);
                $.ajax({
                    url: '{{ route('preferences.store') }}',
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        pref: 'global_search_ctrlf_focus',
                        value: state ? 1 : 0
                    },
                    success: function () {
                        $this.closest('.form-group').addClass('has-success');
                        setTimeout(function () {
                            $this.closest('.form-group').removeClass('has-success');
                        }, 2000);
                    },
                    error: function () {
                        $this.bootstrapSwitch('toggleState', true);
                        $this.closest('.form-group').addClass('has-error');
                        setTimeout(function(){
                            $this.closest('.form-group').removeClass('has-error');
                        }, 2000);
                    }
                });
            });

        $('.ajax-select').on("change", function () {
            var $this = $(this);
            var value = $this.val();
            var pref = $this.data('pref');
            $.ajax({
                url: '{{ route('preferences.store') }}',
                dataType: 'json',
                type: 'POST',
                data: {
                    pref: pref,
                    value: value
                },
                success: function () {
                    if (pref === 'locale') {
                        location.reload();
                    }
                    if (pref === 'site_style') {
                        location.reload();
                    }
                    if (pref === 'timezone') {
                        if(value === 'default') {
                            var tz = window.Intl.DateTimeFormat().resolvedOptions().timeZone;
                            updateTimezone(tz, false);
                        } else {
                            updateTimezone(value, true);
                        }
                    }

                    $this.data('previous', value);
                    $this.closest('.form-group').addClass('has-success');
                    setTimeout(function () {
                        $this.closest('.form-group').removeClass('has-success');
                    }, 2000);
                },
                error: function (data) {
                    $this.val($this.data('previous'));
                    $this.closest('.form-group').addClass('has-error');

                    var json = data.responseJSON;
                    var errors = [];
                    for (var attrib in json) {
                        errors.push(json[attrib]);
                    }
                    toastr.error('Error: ' + errors.join("<br />"));

                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-error');
                    }, 2000);
                }
            });
        });
    </script>
@endsection

@section('css')
    <style>
        #twofactorkeycontainer { display: none; }
    </style>
@endsection
