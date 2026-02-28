@extends('layouts.librenmsv1')

@section('title', __('permissions.rbac.create_role'))

@section('content')
    <div class="tw:min-h-screen tw:bg-gradient-to-br tw:from-slate-50 tw:to-slate-100 tw:dark:from-zinc-950 tw:dark:to-zinc-900 tw:px-6 tw:py-8" x-data="roleForm()">
        <div class="tw:max-w-4xl tw:mx-auto">

            {{-- Header --}}
            <div class="tw:flex tw:items-center tw:justify-between tw:mb-8">
                <div class="tw:flex tw:items-center tw:gap-4">
                    <a href="{{ route('roles.index') }}"
                       class="tw:inline-flex tw:items-center tw:justify-center tw:w-9 tw:h-9 tw:rounded-lg tw:border tw:border-slate-200 tw:dark:border-zinc-700 tw:bg-white tw:dark:bg-zinc-800 tw:text-slate-500 tw:dark:text-zinc-400 tw:hover:text-slate-800 tw:dark:hover:text-zinc-200 tw:hover:border-slate-300 tw:dark:hover:border-zinc-600 tw:shadow-sm tw:transition-all tw:duration-150">
                        <i class="fas fa-arrow-left tw:text-sm"></i>
                    </a>
                    <div>
                        <p class="tw:text-xs tw:font-semibold tw:uppercase tw:tracking-widest tw:text-slate-400 tw:dark:text-zinc-500 tw:mb-0.5">
                            Access Control
                        </p>
                        <h1 class="tw:text-3xl tw:font-black tw:text-slate-800 tw:dark:text-zinc-100 tw:tracking-tight">
                            {{ __('permissions.rbac.create_new_role') }}
                        </h1>
                    </div>
                </div>
                <button type="submit" form="role-form"
                        class="tw:inline-flex tw:items-center tw:gap-2 tw:px-5 tw:py-2.5 tw:rounded-lg tw:bg-indigo-600 tw:hover:bg-indigo-700 tw:text-white tw:font-semibold tw:text-sm tw:shadow-sm tw:transition-all tw:duration-150">
                    <i class="fas fa-save tw:text-indigo-200"></i>
                    {{ __('permissions.rbac.save_role') }}
                </button>
            </div>

            <div class="tw:bg-white tw:dark:bg-zinc-800/50 tw:shadow-sm tw:rounded-xl tw:border tw:border-slate-200 tw:dark:border-zinc-700/50">
                <form action="{{ route('roles.store') }}" method="POST" id="role-form" class="tw:p-8">
                    @csrf
                    @include('roles.form-fields')
                </form>
            </div>
        </div>
    </div>
@endsection
