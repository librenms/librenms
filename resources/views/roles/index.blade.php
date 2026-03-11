@extends('layouts.librenmsv1')

@section('title', __('permissions.rbac.title'))

@section('content')
    <div class="tw:min-h-screen tw:px-6 tw:py-8">

        <div class="tw:max-w-7xl tw:mx-auto">
            <div class="tw:flex tw:justify-between tw:items-start tw:mb-8">
                <div>
                    <div class="tw:text-4xl tw:mb-3 tw:font-black tw:text-slate-800 tw:dark:text-dark-white-100 tw:tracking-tight">
                        {{ __('permissions.rbac.title') }}
                    </div>
                    <div class="tw:mt-1 tw:text-sm tw:text-slate-500 tw:dark:text-dark-white-400">
                        {{ $roles->count() }} {{ Str::plural('role', $roles->count()) }} configured
                    </div>
                </div>
                <div class="tw:flex tw:items-center tw:gap-3">
                    @can('viewAny', \App\Models\User::class)
                        <a href="{{ route('users.index') }}"
                           class="tw:inline-flex tw:items-center tw:gap-2 tw:px-4 tw:py-2.5 tw:rounded-lg tw:border tw:border-slate-200 tw:dark:border-dark-gray-100 tw:bg-white tw:dark:bg-dark-gray-400 tw:text-slate-700 tw:dark:text-dark-white-200 tw:font-semibold tw:shadow-sm tw:hover:bg-slate-50 tw:dark:hover:bg-dark-gray-300 tw:hover:border-slate-300 tw:dark:hover:border-dark-gray-100 tw:transition-all tw:duration-150 tw:no-underline">
                            <i class="fas fa-users tw:text-slate-400 tw:dark:text-dark-white-400"></i>
                            {{ __('permissions.rbac.manage_users') }}
                        </a>
                    @endcan
                    <a href="{{ route('roles.create') }}"
                       class="tw:inline-flex tw:items-center tw:gap-2 tw:px-5 tw:py-2.5 tw:rounded-lg tw:bg-sky-700 tw:hover:bg-sky-800 tw:text-white tw:font-semibold tw:shadow-sm tw:transition-all tw:duration-150 tw:no-underline">
                        <i class="fas fa-plus tw:text-white"></i>
                        {{ __('permissions.rbac.add_role') }}
                    </a>
                </div>
            </div>

            {{-- Roles Grid --}}
            <div class="tw:grid tw:gap-3">
                @foreach($roles as $role)
                    <div class="tw:group tw:relative tw:bg-white tw:dark:bg-dark-gray-400 tw:border tw:border-slate-200 tw:dark:border-dark-gray-200 tw:rounded-xl tw:px-6 tw:py-5 tw:shadow-sm tw:hover:shadow-md tw:hover:border-slate-300 tw:dark:hover:border-dark-gray-100 tw:transition-all tw:duration-200">

                        {{-- Left accent bar --}}
                        <div class="tw:absolute tw:left-0 tw:top-4 tw:bottom-4 tw:w-1 tw:rounded-r-full tw:bg-indigo-500 tw:opacity-0 tw:group-hover:opacity-100 tw:transition-opacity tw:duration-200"></div>

                        <div class="tw:flex tw:items-center tw:gap-6">

                            {{-- Role icon + name --}}
                            <div class="tw:flex tw:items-center tw:gap-4 tw:min-w-60">
                                <div class="tw:flex-shrink-0 tw:w-10 tw:h-10 tw:rounded-lg tw:bg-indigo-50 tw:dark:bg-dark-gray-300 tw:flex tw:items-center tw:justify-center tw:border tw:border-indigo-100 tw:dark:border-dark-gray-200">
                                    <i class="fas fa-shield-halved tw:text-indigo-500 tw:dark:text-dark-white-300"></i>
                                </div>
                                <div>
                                    <div class="tw:text-xl tw:font-bold tw:text-slate-800 tw:dark:text-dark-white-100">
                                        {{ $role->name }}
                                    </div>
                                    <div class="tw:text-base tw:text-slate-400 tw:dark:text-dark-white-400 tw:mt-1">
                                        {{ $role->permissions->count() }} {{ Str::plural('permission', $role->permissions->count()) }}
                                    </div>
                                </div>
                            </div>

                            {{-- Divider --}}
                            <div class="tw:w-px tw:self-stretch tw:bg-slate-100 tw:dark:bg-dark-gray-200"></div>

                            {{-- Permissions --}}
                            <div class="tw:flex-1 tw:flex tw:flex-wrap tw:gap-1.5 tw:py-1">
                                @forelse($role->permissions as $permission)
                                    <span class="tw:text-sm tw:inline-flex tw:items-center tw:gap-1.5 tw:bg-slate-100 tw:dark:bg-dark-gray-300 tw:text-slate-600 tw:dark:text-dark-white-300 tw:px-2.5 tw:py-1 tw:rounded-md tw:font-medium tw:border tw:border-slate-200/80 tw:dark:border-dark-gray-100">
                                <span class="tw:w-1.5 tw:h-1.5 tw:rounded-full tw:bg-emerald-400 tw:dark:bg-emerald-500 tw:flex-shrink-0"></span>
                                {{ $permission->name }}
                            </span>
                                @empty
                                    <span class="tw:inline-flex tw:items-center tw:gap-1.5 tw:text-slate-400 tw:dark:text-dark-white-400 tw:italic">
                                    @if($role->name == 'admin')
                                        <span class="tw:w-1.5 tw:h-1.5 tw:rounded-full tw:bg-emerald-400 tw:dark:bg-emerald-500 "></span>
                                        {{ __('permissions.rbac.all_permissions') }}
                                    @elseif($role->name == 'global-read')
                                        <span class="tw:w-1.5 tw:h-1.5 tw:rounded-full tw:bg-blue-400 tw:dark:bg-blue-500 "></span>
                                        {{ __('permissions.rbac.read_permissions') }}
                                    @else
                                        <span class="tw:w-1.5 tw:h-1.5 tw:rounded-full tw:bg-slate-300 tw:dark:bg-dark-gray-100"></span>
                                        {{ __('permissions.rbac.no_permissions') }}
                                    @endif
                                    </span>
                                @endforelse
                            </div>

                            {{-- Actions --}}
                            <div class="tw:flex tw:items-center tw:gap-2 tw:flex-shrink-0">
                                @if(in_array($role->name, $protected))
                                    <div class="tw:w-9 tw:h-9 tw:flex tw:items-center tw:justify-center">
                                        <i class="fas fa-lock tw:text-slate-300 tw:dark:text-dark-gray-100" title="Protected role"></i>
                                    </div>
                                @else
                                    <a href="{{ route('roles.edit', $role->id) }}"
                                       title="{{ __('permissions.rbac.edit_role') }}"
                                       class="tw:inline-flex tw:items-center tw:justify-center tw:w-9 tw:h-9 tw:rounded-lg tw:text-slate-400 tw:dark:text-dark-white-400 tw:hover:text-indigo-600 tw:dark:hover:text-dark-white-100 tw:hover:bg-indigo-50 tw:dark:hover:bg-dark-gray-300 tw:transition-all tw:duration-150">
                                        <i class="fas fa-lg fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                          onsubmit="return confirm('{{ __('permissions.rbac.confirm_delete') }}');"
                                          class="tw:inline-flex">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="{{ __('permissions.rbac.delete_role') }}"
                                                class="tw:inline-flex tw:items-center tw:justify-center tw:w-9 tw:h-9 tw:rounded-lg tw:text-slate-400 tw:dark:text-dark-red-400 tw:hover:text-red-600 tw:dark:hover:text-red-600 tw:transition-all tw:duration-150">
                                            <i class="fas fa-lg fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Empty state --}}
            @if($roles->isEmpty())
                <div class="tw:flex tw:flex-col tw:items-center tw:justify-center tw:py-24 tw:text-center">
                    <div class="tw:w-16 tw:h-16 tw:rounded-2xl tw:bg-slate-100 tw:dark:bg-dark-gray-400 tw:flex tw:items-center tw:justify-center tw:mb-4 tw:border tw:border-slate-200 tw:dark:border-dark-gray-200">
                        <i class="fas fa-shield-halved tw:text-slate-300 tw:dark:text-dark-gray-100 tw:text-2xl"></i>
                    </div>
                    <h3 class="tw:text-slate-700 tw:dark:text-dark-white-200 tw:font-semibold tw:text-lg tw:mb-1">No roles yet</h3>
                    <p class="tw:text-slate-400 tw:dark:text-dark-white-400 tw:text-sm tw:mb-6">Create your first role to start managing access control.</p>
                    <a href="{{ route('roles.create') }}"
                       class="tw:inline-flex tw:items-center tw:gap-2 tw:px-5 tw:py-2.5 tw:rounded-lg tw:bg-indigo-600 tw:hover:bg-indigo-700 tw:text-white tw:font-semibold tw:text-sm tw:transition-colors tw:duration-150">
                        <i class="fas fa-plus"></i> {{ __('permissions.rbac.add_role') }}
                    </a>
                </div>
            @endif

        </div>
    </div>
@endsection
