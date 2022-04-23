@extends('layouts.librenmsv1')

@section('title', __('Manage Users'))

@section('content')
<div class="container-fluid">
    <x-panel>
        <x-slot name="title">
            <i class="fa fa-user-circle-o fa-fw fa-lg" aria-hidden="true"></i> {{ __('Manage Users') }}
        </x-slot>

        <div class="table-responsive">
            <table id="users" class="table table-bordered table-condensed" style="display: none;">
                <thead>
                <tr>
                    <th data-column-id="user_id" data-visible="false" data-identifier="true" data-type="numeric">{{ __('ID') }}</th>
                    <th data-column-id="username">{{ __('Username') }}</th>
                    <th data-column-id="realname">{{ __('Real Name') }}</th>
                    <th data-column-id="level" data-formatter="level" data-type="numeric">{{ __('Access') }}</th>
                    <th data-column-id="auth_type" data-visible="{{ $multiauth ? 'true' : 'false' }}">{{ __('auth.title') }}</th>
                    <th data-column-id="email">{{ __('Email') }}</th>
                    @if(\LibreNMS\Authentication\LegacyAuth::getType() == 'mysql')
                    <th data-column-id="enabled" data-formatter="enabled">{{ __('Enabled') }}</th>
                    @endif
                    @config('twofactor')
                    <th data-column-id="twofactor" data-formatter="twofactor">{{ __('2FA') }}</th>
                    @endconfig
                    <th data-column-id="descr">{{ __('Description') }}</th>
                    <th data-column-id="action" data-formatter="actions" data-sortable="false" data-searchable="false">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody id="users_rows">
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->user_id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->realname }}</td>
                            <td>{{ $user->level }}</td>
                            <td>{{ $user->auth_type }}</td>
                            <td>{{ $user->email }}</td>
                            @if(\LibreNMS\Authentication\LegacyAuth::getType() == 'mysql')
                            <td>{{ $user->enabled }}</td>
                            @endif
                            @config('twofactor')
                                @if(\App\Models\UserPref::getPref($user, 'twofactor'))
                                <td>1</td>
                                @endif
                            @endconfig
                            <td>{{ $user->descr }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-panel>
</div>
@endsection

@section('javascript')
    <script type="application/javascript">
        $(document).ready(function(){
            var user_grid = $("#users");
            user_grid.bootgrid({
                formatters: {
                    enabled: function (column, row) {
                        if (row['enabled'] == 1) {
                            return '<span class="fa fa-fw fa-check text-success"></span>';
                        } else {
                            return '<span class="fa fa-fw fa-close text-danger"></span>';
                        }
                    },
                    twofactor: function (column, row) {
                        if(row['twofactor'] == 1) {
                            return '<span class="fa fa-fw fa-check text-success"></span>';
                        }
                    },
                    actions: function (column, row) {
                        var edit_button = '<form action="{{ route('users.edit', ':user_id') }}'.replace(':user_id', row['user_id']) + '" method="GET">' +
                            '@csrf' +
                            '<button type="submit" title="{{ __('Edit') }}" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></button>' +
                            '</form> ';

                        var delete_button = '<button type="button" title="{{ __('Delete') }}" class="btn btn-sm btn-danger" onclick="return delete_user(' + row['user_id'] + ', \'' + row['username'] + '\');">' +
                            '<i class="fa fa-trash"></i></button> ';

                        var manage_button = '<form action="{{ url('edituser') }}/" method="GET"';

                        if (row['level'] >= 5) {
                            manage_button += ' style="visibility:hidden;"'
                        }

                        manage_button += '>@csrf<input type="hidden" name="user_id" value="' + row['user_id'] +
                            '"><button type="submit" title="{{ __('Manage Access') }}" class="btn btn-sm btn-primary"><i class="fa fa-tasks"></i></button>' +
                            '</form> ';

                        var output = manage_button + edit_button;
                        if ('{{ Auth::id() }}' != row['user_id']) {
                            output += delete_button;
                        }

                        return output
                    },
                    level: function (column, row) {
                        var level = row[column.id];
                        if (level == 10) {
                            return '{{ __('Admin') }}';
                        } else if (level == 5) {
                            return '{{ __('Global Read') }}';
                        } else if (level == 11) {
                            return '{{ __('Demo') }}';
                        }

                        return '{{ __('Normal') }}';
                    }
                }
            });

            @if(\LibreNMS\Config::get('auth_mechanism') == 'mysql')
                $('.actionBar').append('<div class="pull-left"><a href="{{ route('users.create') }}" type="button" class="btn btn-primary">{{ __('Add User') }}</a></div>');
            @endif

            user_grid.css('display', 'table'); // done loading, show
        });

        function delete_user(user_id, username, url)
        {
            if (confirm('{{ __('Are you sure you want to delete ') }}' + username + '?')) {
                $.ajax({
                    url: '{{ route('users.destroy', ':user_id') }}'.replace(':user_id', user_id),
                    type: 'DELETE',
                    success: function (msg) {
                        $("#users").bootgrid("remove", [user_id]);
                        toastr.success(msg);
                    },
                    error: function () {
                        toastr.error('{{ __('The user could not be deleted') }}');
                    }
                });
            }

            return false;
        }
    </script>
@endsection

@section('css')
<style>
    #users form { display:inline; }
</style>
@endsection
