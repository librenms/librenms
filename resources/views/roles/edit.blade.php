@extends('layouts.librenmsv1')

@section('title', __('permissions.rbac.edit_role'))

@section('content')
    <div class="container" x-data="roleForm({{ json_encode($role->permissions->pluck('name')) }})">
        <x-panel>
            <x-slot name="title">
                <div class="tw:flex tw:items-center tw:justify-between tw:w-full">
                    <div class="tw:flex tw:items-center">
                        <a href="{{ route('roles.index') }}" class="tw:mr-2 tw:text-inherit">
                            <i class="fas fa-arrow-left fa-fw" aria-hidden="true"></i>
                        </a>
                        {{ __('permissions.rbac.edit_role') }}: {{ $role->name }}
                    </div>
                    <div class="tw:flex tw:gap-2">
                        <a href="{{ route('roles.index') }}" class="btn btn-sm btn-default">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" form="role-form" class="btn btn-sm btn-primary">
                            <i class="fas fa-save tw:mr-1"></i>
                            {{ __('permissions.rbac.update_role') }}
                        </button>
                    </div>
                </div>
            </x-slot>

            <form action="{{ route('roles.update', $role->id) }}" method="POST" id="role-form" class="form-horizontal">
                @csrf
                @method('PUT')
                @include('roles.form-fields')
            </form>
        </x-panel>
    </div>
@endsection
