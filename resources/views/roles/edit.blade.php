@extends('layouts.librenmsv1')

@section('title', __('permissions.rbac.edit_role'))

@section('content')
    <div class="tw:min-h-screen tw:px-6 tw:py-8" x-data="roleForm({{ json_encode($role->permissions->pluck('name')) }})">
        <div class="tw:max-w-4xl tw:mx-auto">

            {{-- Header --}}
            <div class="tw:flex tw:items-center tw:justify-between tw:mb-8">
                <div class="tw:flex tw:items-center tw:gap-4">
                    <a href="{{ route('roles.index') }}"
                       class="tw:inline-flex tw:items-center tw:justify-center tw:w-9 tw:h-9 tw:mt-1 tw:rounded-lg tw:border tw:border-slate-200 tw:dark:border-dark-gray-100 tw:bg-white tw:dark:bg-dark-gray-400 tw:text-slate-500 tw:dark:text-dark-white-400 tw:hover:text-slate-800 tw:dark:hover:text-dark-white-100 tw:hover:border-slate-300 tw:dark:hover:border-dark-gray-100 tw:shadow-sm tw:transition-all tw:duration-150">
                        <i class="fas fa-lg fa-arrow-left"></i>
                    </a>
                    <div class="tw:text-4xl tw:font-black tw:text-slate-800 tw:dark:text-dark-white-100 tw:tracking-tight">
                        {{ __('permissions.rbac.edit_role') }}: <span class="tw:text-indigo-600 tw:dark:text-dark-white-300">{{ $role->name }}</span>
                    </div>
                </div>
                <button type="submit" form="role-form"
                        class="tw:inline-flex tw:items-center tw:gap-2 tw:px-5 tw:py-2.5 tw:rounded-lg tw:bg-indigo-600 tw:hover:bg-indigo-700 tw:text-white tw:font-semibold tw:shadow-sm tw:transition-all tw:duration-150">
                    <i class="fas fa-save tw:text-indigo-200"></i>
                    {{ __('permissions.rbac.update_role') }}
                </button>
            </div>

            <div class="tw:bg-white tw:dark:bg-dark-gray-400 tw:shadow-sm tw:rounded-xl tw:border tw:border-slate-200 tw:dark:border-dark-gray-200">
                <form action="{{ route('roles.update', $role->id) }}" method="POST" id="role-form" class="tw:p-8">
                    @csrf
                    @method('PUT')
                    @include('roles.form-fields')
                </form>
            </div>
        </div>
    </div>
@endsection
