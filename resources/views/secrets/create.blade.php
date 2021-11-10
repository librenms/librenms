@extends('layouts.librenmsv1')

@section('title', __('Add Credential'))

@section('content')
    <div class="container">
        <div class="tw:mb-4">
            <a href="{{ route('secrets.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left tw:mr-1"></i> {{ __('Back to Credentials') }}
            </a>
        </div>

        <x-panel>
            <x-slot name="title">
                <i class="fas fa-plus fa-fw fa-lg" aria-hidden="true"></i> {{ __('Add Credential') }}
            </x-slot>

            <div class="tw:mb-6">
                <form method="GET" action="{{ route('secrets.create') }}" class="tw:flex tw:items-center tw:gap-4">
                    <label for="type_selector" class="tw:font-medium tw:mb-0">{{ __('Select Credential Type:') }}</label>
                    <select id="type_selector" name="type" class="form-control tw:w-auto" onchange="this.form.submit()">
                        @foreach($types as $type)
                            <option value="{{ $type->value }}" {{ $currentType === $type ? 'selected' : '' }}>
                                {{ Str::upper($type->value) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <hr class="tw:my-6">

            <form method="POST" action="{{ route('secrets.store') }}">
                @csrf
                <input type="hidden" name="secret_type" value="{{ $currentType->value }}">

                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                    <label for="description" class="control-label">{{ __('Description') }}</label>
                    <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}" required autofocus>
                    @if($errors->has('description'))
                        <span class="help-block">{{ $errors->first('description') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="default" value="1" {{ old('default') ? 'checked' : '' }}> {{ __('Default') }}
                        </label>
                    </div>
                </div>

                @include('secrets._form_fields', ['schema' => $schema, 'data' => []])

                <div class="tw:mt-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save tw:mr-1"></i> {{ __('Save') }}
                    </button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
