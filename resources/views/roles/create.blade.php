@extends('layouts.librenmsv1')

@section('title', __('permissions.rbac.create_role'))

@section('content')
    <div class="container" x-data="roleForm()">
        <x-panel>
            <x-slot name="title">
                <div class="tw:flex tw:items-center tw:justify-between tw:w-full">
                    <div class="tw:flex tw:items-center">
                        <a href="{{ route('roles.index') }}" class="tw:mr-2 tw:text-inherit">
                            <i class="fas fa-arrow-left fa-fw" aria-hidden="true"></i>
                        </a>
                        {{ __('permissions.rbac.create_new_role') }}
                    </div>
                    <div class="tw:flex tw:gap-2">
                        <a href="{{ route('roles.index') }}" class="btn btn-sm btn-default">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" form="role-form" class="btn btn-sm btn-primary">
                            <i class="fas fa-save tw:mr-1"></i>
                            {{ __('permissions.rbac.save_role') }}
                        </button>
                    </div>
                </div>
            </x-slot>

            <form action="{{ route('roles.store') }}" method="POST" id="role-form" class="form-horizontal">
                @csrf
                @include('roles.form-fields')
            </form>
        </x-panel>
    </div>
@endsection
