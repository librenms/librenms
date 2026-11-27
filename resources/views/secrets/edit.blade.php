@extends('layouts.librenmsv1')

@section('title', __('Edit Secret'))

@section('content')
    <div class="container">
        <div class="tw:mb-4">
            <a href="{{ route('secrets.index') }}" class="btn btn-default">
                <i class="fa-solid fa-arrow-left tw:mr-1"></i> {{ __('Back to Secrets') }}
            </a>
        </div>

        <x-panel>
            <x-slot name="title">
                <i class="fa-solid fa-pencil fa-fw fa-lg" aria-hidden="true"></i> {{ __('Edit Secret') }} - {{ $secret->typeLabel() }}
            </x-slot>

            <form method="POST" action="{{ route('secrets.update', $secret->id) }}"
                  x-data="{ formData: @js($data) }">
                @csrf
                @method('PUT')

                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                    <label for="description" class="control-label">{{ __('Description') }}</label>
                    <input type="text" class="form-control" id="description" name="description" value="{{ old('description', $secret->description) }}" required autofocus>
                    @if($errors->has('description'))
                        <span class="help-block">{{ $errors->first('description') }}</span>
                    @endif
                </div>

                @if($secret->secret_type === \LibreNMS\Enum\SecretType::Unknown)
                    <div class="alert alert-warning">{{ __('poller.unknown_secret_type', ['type' => $secret->typeName()]) }}</div>
                    @if($data)
                        <dl class="dl-horizontal">
                            @foreach($data as $key => $value)
                                <dt>{{ $key }}</dt>
                                <dd class="tw:font-mono tw:break-all">{{ is_scalar($value) ? $value : json_encode($value) }}</dd>
                            @endforeach
                        </dl>
                    @endif
                @endif

                <x-field-schema-fields
                    :fields="$schema"
                    :data="$data"
                    :method-type="$secret->typeName()"
                    model-prefix="formData"
                    :check-can-unmask="true"
                />

                <div class="tw:mt-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk tw:mr-1"></i> {{ __('Save') }}
                    </button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
