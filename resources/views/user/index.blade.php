@extends('layouts.librenmsv1')

@section('title', __('Manage Users'))

@section('content')
<div class="container-fluid">
    <x-panel>
        <x-slot name="title">
            <i class="fa fa-user-circle-o fa-fw fa-lg" aria-hidden="true"></i> {{ __('Manage Users') }}
        </x-slot>

        <div class="table-responsive">
            <table id="users" class="table table-bordered table-condensed">
                <thead>
                <tr>
                    <th data-column-id="user_id" data-visible="false" data-identifier="true" data-type="numeric">{{ __('ID') }}</th>
                    <th data-column-id="username" data-formatter="text">{{ __('Username') }}</th>
                    <th data-column-id="realname" data-formatter="text">{{ __('Real Name') }}</th>
                    <th data-column-id="roles" data-formatter="roles">{{ __('Roles') }}</th>
                    <th data-column-id="auth_type" data-visible="{{ $multiauth ? 'true' : 'false' }}">{{ __('auth.title') }}</th>
                    <th data-column-id="email" data-formatter="text">{{ __('Email') }}</th>
                    <th data-column-id="timezone">{{ __('Timezone') }}</th>
                    @if(\LibreNMS\Authentication\LegacyAuth::getType() == 'mysql')
                    <th data-column-id="enabled" data-formatter="enabled">{{ __('Enabled') }}</th>
                    @endif
                    @config('twofactor')
                    <th data-column-id="twofactor" data-formatter="twofactor">{{ __('2FA') }}</th>
                    @endconfig
                    <th data-column-id="descr" data-formatter="text">{{ __('Description') }}</th>
                    <th data-column-id="action" data-formatter="actions" data-sortable="false" data-searchable="false">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody id="users_rows">
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->user_id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->realname }}</td>
                            <td>{{ $user->roles->pluck('title') }}</td>
                            <td>{{ $user->auth_type }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ \App\Models\UserPref::getPref($user, 'timezone') ?: "Browser Timezone" }}</td>
                            @if(\LibreNMS\Authentication\LegacyAuth::getType() == 'mysql')
                            <td>{{ $user->enabled }}</td>
                            @endif
                            @config('twofactor')
                                @if(\App\Models\UserPref::getPref($user, 'twofactor'))
                                <td>1</td>
                                @else
                                <td></td>
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
                    text: function (column, row) {
                        let div = document.createElement('div');
                        div.innerText = row[column.id];
                        return div.innerHTML;
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

                        // FIXME don't show for super admin
                        var manage_button = '<form action="{{ url('edituser') }}/" method="GET"';
                        manage_button += '>@csrf<input type="hidden" name="user_id" value="' + row['user_id'] +
                            '"><button type="submit" title="{{ __('Manage Access') }}" class="btn btn-sm btn-primary"><i class="fa fa-tasks"></i></button>' +
                            '</form> ';

                        var output = manage_button + edit_button;
                        if ('{{ Auth::id() }}' != row['user_id']) {
                            output += delete_button;
                        }

                        return output
                    },
                    roles: function (column, row) {
                        let roles = JSON.parse(row[column.id]);
                        let div = document.createElement('div');

                        roles.forEach((role) => {
                            let label = document.createElement('span');
                            label.className = 'label label-info tw-mr-1';
                            label.innerText = role;
                            div.appendChild(label);
                        })

                        return div.outerHTML;
                    }
                }
            });

            @can('create', \App\Models\User::class)
                $('.actionBar').append('<div class="pull-left"><a href="{{ route('users.create') }}" type="button" class="btn btn-primary">{{ __('Add User') }}</a></div>');
            @endcan

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
