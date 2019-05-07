@extends('layouts.librenmsv1')

@section('title', __('Preferences'))

@section('content')
<div class="container">

            @if(\LibreNMS\Authentication\LegacyAuth::get()->canUpdatePasswords(Auth::user()->username))
        <div class="panel panel-default panel-condensed">
            <div class="panel-heading">@lang('Change Password')</div>
            <div class="panel-body">
            <form method='post' action='preferences/' class='form-horizontal' role='form'>
            <input type=hidden name='action' value='changepass'>
            <div class='form-group'>
                <label for='old_pass' class='col-sm-2 control-label'>Current Password</label>
                <div class='col-sm-4'>
                    <input type=password name=old_pass autocomplete='off' class='form-control input-sm'>
                </div>
                <div class='col-sm-6'>
                </div>
            </div>
            <div class='form-group'>
                <label for='new_pass' class='col-sm-2 control-label'>New Password</label>
                <div class='col-sm-4'>
                    <input type=password name=new_pass autocomplete='off' class='form-control input-sm'>
                </div>
                <div class='col-sm-6'>
                </div>
            </div>
            <div class='form-group'>
                <label for='new_pass2' class='col-sm-2 control-label'>New Password</label>
                <div class='col-sm-4'>
                    <input type=password name=new_pass2 autocomplete='off' class='form-control input-sm'>
                    <br>
                    <center><button type='submit' class='btn btn-default'>Submit</button></center>
                </div>
                <div class='col-sm-6'>
                </div>
            </div>

        </form>
        @endif

<div class="panel panel-default panel-condensed">
    <div class="panel-heading">@lang('Default Dashboard')</div>
    <div class="panel-body">
                <form method='post' action='preferences/' class='form-horizontal' role='form'>
                    <div class='form-group'>
                        <input type=hidden name='action' value='changedash'>
                        <div class='form-group'>
                            <label for='dashboard' class='col-sm-2 control-label'>Dashboard</label>
                            <div class='col-sm-4'>
                                <select class='form-control' name='dashboard'>
                                    <option value='1'>murrant:Default</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2"><button type="submit" class="btn btn-default">Update Dashboard</button></div>
                        </div>
                    </div>
                </form>
    </div>
</div>

    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">@lang('Add schedule notes to devices notes')</div>
        <div class="panel-body">
                <form method='post' action='preferences/' class='form-horizontal' role='form'>
                    <div class='form-group'>
                        <input type=hidden name='action' value='changenote'>
                        <div class='form-group'>
                            <label for='dashboard' class='col-sm-3 control-label'>Add schedule notes to devices notes</label>
                            <div class='col-sm-4'>
                                <input id='notetodevice' type='checkbox' name='notetodevice' data-size='small' >
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-4 col-sm-offset-3'>
                                <button type='submit' class='btn btn-default'>Update preferences</button>
                            </div>
                            <div class='col-sm-6'></div>
                        </div>
                    </div>
                </form>
        </div>
    </div>


        <div class="panel panel-default panel-condensed">
            <div class="panel-heading">@lang('Device Permissions')</div>
            <div class="panel-body">
            <strong class='blue'>Global Administrative Access</strong>
            </div>
        </div>

            <script>$("[name='notetodevice']").bootstrapSwitch('offColor','danger');</script>
        </div>

</div>

@endsection
