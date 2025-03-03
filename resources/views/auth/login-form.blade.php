<x-panel>
    <x-slot name="title">
        <img class="logon-logo img-responsive" src="{{ asset(\LibreNMS\Config::get('title_image', 'images/librenms_logo_light.svg')) }}">
    </x-slot>

    @config('login_message')
    <x-slot name="footer" class="text-center">
        <div class="logon-message">{{ \LibreNMS\Config::get('login_message') }}</div>
    </x-slot>
    @endconfig

    <div class="container-fluid">
        <form class="form-horizontal" role="form" action="{{ url('login') }}" method="post" name="logonform">
            {{ csrf_field() }}
            <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                <div class="col-md-12">
                    <input type="text" name="username" id="username" value="{{ old('username') }}" class="form-control" placeholder="{{ __('Username') }}" required autofocus />
                    @if ($errors->has('email'))
                        <span class="help-block">
                                    <strong>{{ $errors->first('username') }}</strong>
                                </span>
                    @endif
                </div>
            </div>
            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                <div class="col-md-12">
                    <input type="password" name="password" id="password" autocomplete="off" class="form-control" placeholder="{{ __('Password') }}" />
                    @if ($errors->has('password'))
                        <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="remember" id="remember" /> {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <button type="submit" id="login" class="btn btn-primary btn-block" name="submit">
                        <i class="fa fa-btn fa-sign-in"></i> {{ __('Login') }}
                    </button>
                </div>
            </div>
        </form>

        @foreach (\LibreNMS\Config::get('auth.socialite.configs', []) as $provider => $config)
        <form role="form" action="{{ route('socialite.redirect', $provider) }}" method="post" class="tw-mt-5">
            {{ csrf_field() }}
            <button type="submit" id="login" class="btn btn-success btn-block">
                <i class="fab fa-btn fa-{{ $provider }}"></i> {{ __('Login with') }} {{ ucfirst($provider) }}
            </button>
        </form>
        @endforeach
    </div>
</x-panel>
