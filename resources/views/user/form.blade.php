<div class="form-group {{ $errors->has('realname') ? 'has-error' : '' }}">
    <label for="realname" class="control-label col-sm-3 text-nowrap">{{ __('Real Name') }}</label>
    <div class="col-sm-9">
        <input type="text" class="form-control" id="realname" name="realname" value="{{ old('realname', $user->realname) }}">
        <span class="help-block">{{ $errors->first('realname') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('enabled')) has-error @endif">
    <label for="enabled" class="control-label col-sm-3">{{ __('Enabled') }}</label>
    <div class="col-sm-9">
        <input type="hidden" value="@if(Auth::id() == $user->user_id) 1 @else 0 @endif" name="enabled">
        <input type="checkbox" id="enabled" name="enabled" data-size="small" @if(old('enabled', $user->enabled ?? true)) checked @endif @if(Auth::id() == $user->user_id) disabled @endif>
    </div>
</div>

<div class="form-group @if($errors->has('email')) has-error @endif">
    <label for="email" class="control-label col-sm-3">{{ __('Email') }}</label>
    <div class="col-sm-9">
        <input type="text" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}">
        <span class="help-block">{{ $errors->first('email') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('descr')) has-error @endif">
    <label for="descr" class="control-label col-sm-3">{{ __('Description') }}</label>
    <div class="col-sm-9">
        <input type="text" class="form-control" id="descr" name="descr" value="{{ old('descr', $user->descr) }}">
        <span class="help-block">{{ $errors->first('descr') }}</span>
    </div>
</div>

@can('viewAny', Bouncer::role())
    <div class="form-group @if($errors->has('roles')) has-error @endif">
        <label for="level" class="control-label col-sm-3">{{ __('Roles') }}</label>
        <div class="col-sm-9">
            <select class="form-control" id="roles" name="roles[]" multiple @cannot('manage', Bouncer::role()) readonly @endcannot>
                @foreach(Bouncer::role()->all() as $role)
                    <option value="{{ $role->name }}" @if(collect(old('roles', $user->roles->pluck('name')))->contains($role->name)) selected @endif>{{ __($role->title) }}</option>
                @endforeach
            </select>
            <span class="help-block">{{ $errors->first('roles') }}</span>
        </div>
    </div>
@endcan

<div class="form-group @if($errors->has('dashboard')) has-error @endif">
    <label for="dashboard" class="control-label col-sm-3">{{ __('Dashboard') }}</label>
    <div class="col-sm-9">
        <select id="dashboard" name="dashboard" class="form-control">
            @foreach($dashboards as $dash)
                <option value="{{ $dash->dashboard_id }}" @if(old('dashboard', $dashboard) == $dash->dashboard_id) selected @endif>{{ $dash->dashboard_name }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('dashboard') }}</span>
    </div>
</div>

@if($user->canSetPassword(auth()->user()))
    <div class="form-group @if($errors->hasAny(['old_password', 'new_password', 'new_password_confirmation'])) has-error @endif">
        <label for="password" class="control-label col-sm-3">{{ __('Password') }}</label>
        <div class="col-sm-9">
            @if(auth()->user()->cannot('admin') || auth()->user()->is($user))
                <input type="password" class="form-control" id="old_password" name="old_password" placeholder="{{ __('Current Password') }}">
            @endif
            <input type="password" autocomplete="off" class="form-control" id="new_password" name="new_password" placeholder="{{ __('New Password') }}">
            <input type="password" autocomplete="off" class="form-control" id="new_password_confirmation" name="new_password_confirmation" placeholder="{{ __('Confirm Password') }}">
            <span class="help-block">
                @foreach($errors->get('*password*') as $error)
                    {{ implode(' ', $error) }}
                @endforeach
            </span>
        </div>
    </div>
@endif

@if(\LibreNMS\Authentication\LegacyAuth::get()->canUpdatePasswords())
<div class="form-group @if($errors->has('can_modify_passwd')) has-error @endif">
    <label for="can_modify_passwd" class="control-label col-sm-3">{{ __('Can Modify Password') }}</label>
    <div class="col-sm-9">
        <input type="hidden" value="0" name="can_modify_passwd">
        <input type="checkbox" id="can_modify_passwd" name="can_modify_passwd" data-size="small" @if(old('can_modify_passwd', $user->can_modify_passwd)) checked @endif>
        <span class="help-block">{{ $errors->first('can_modify_passwd') }}</span>
    </div>
</div>
@endif

<div class="form-group @if($errors->has('timezone')) has-error @endif">
    <label for="timezone" class="control-label col-sm-3">{{ __('Timezone') }}</label>
    <div class="col-sm-9">
        <select id="timezone" name="timezone" class="form-control">
            <option value="default">Browser Timezone</option>
            @foreach(timezone_identifiers_list() as $tz)
                <option value="{{ $tz }}" @if(old('timezone', $timezone) == $tz) selected @endif>{{ $tz }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('timezone') }}</span>
    </div>
</div>

<script>
$("[type='checkbox']").bootstrapSwitch();
</script>
