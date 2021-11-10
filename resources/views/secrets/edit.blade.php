@extends('layouts.librenmsv1')

@section('title', __('Edit Secret'))

@section('content')
    <div class="container">
        <div class="tw:mb-4">
            <a href="{{ route('secrets.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left tw:mr-1"></i> {{ __('Back to Secrets') }}
            </a>
        </div>

        <x-panel>
            <x-slot name="title">
                <i class="fas fa-pencil fa-fw fa-lg" aria-hidden="true"></i> {{ __('Edit Secret') }} - {{ Str::upper($secret->secret_type->value) }}
            </x-slot>

            <form method="POST" action="{{ route('secrets.update', $secret->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                    <label for="description" class="control-label">{{ __('Description') }}</label>
                    <input type="text" class="form-control" id="description" name="description" value="{{ old('description', $secret->description) }}" required autofocus>
                    @if($errors->has('description'))
                        <span class="help-block">{{ $errors->first('description') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="default" value="1" {{ old('default', $secret->default) ? 'checked' : '' }}> {{ __('Default') }}
                        </label>
                    </div>
                </div>

                @include('secrets._form_fields', ['schema' => $schema, 'data' => $data])

                <div class="tw:mt-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save tw:mr-1"></i> {{ __('Save') }}
                    </button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
