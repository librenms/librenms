@foreach($fields as $field)
    <div class="form-group" @if($field['visible_if_expression']) x-show="{{ $field['visible_if_expression'] }}" @endif>
        <label class="control-label">{{ __($field['label']) }}</label>

        @if($field['field_type'] === 'select')
            <select name="{{ $namePrefix }}[{{ $field['key'] }}]"
                    @if(!empty($modelPrefix)) x-model="{{ $modelPrefix }}['{{ $field['key'] }}']" @endif
                    class="form-control">
                @foreach($field['options'] as $optVal => $optLabel)
                    <option value="{{ $optVal }}">{{ __($optLabel) }}</option>
                @endforeach
            </select>
        @elseif($field['field_type'] === 'password')
            @if(!empty($checkCanUnmask))
                @can('unmask', \App\Models\Secret::class)
                    <div class="input-group tw:w-full">
                        <input type="password"
                               id="secret_{{ $methodType }}_{{ $field['key'] }}"
                               name="{{ $namePrefix }}[{{ $field['key'] }}]"
                               @if(!empty($modelPrefix)) x-model="{{ $modelPrefix }}['{{ $field['key'] }}']" @endif
                               class="form-control"
                               autocomplete="new-password">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-toggle-password" onclick="togglePasswordVisibility('secret_{{ $methodType }}_{{ $field['key'] }}', this)" title="{{ __('Show/hide') }}">
                                <i class="fa fa-eye-slash"></i>
                            </button>
                        </span>
                    </div>
                @else
                    <input type="password" name="{{ $namePrefix }}[{{ $field['key'] }}]" value="********" class="form-control" readonly>
                @endcan
            @else
                <div class="input-group tw:w-full">
                    <input type="password"
                           id="secret_{{ $methodType }}_{{ $field['key'] }}"
                           name="{{ $namePrefix }}[{{ $field['key'] }}]"
                           @if(!empty($modelPrefix)) x-model="{{ $modelPrefix }}['{{ $field['key'] }}']" @endif
                           class="form-control"
                           autocomplete="new-password">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-toggle-password" onclick="togglePasswordVisibility('secret_{{ $methodType }}_{{ $field['key'] }}', this)" title="{{ __('Show/hide') }}">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </span>
                </div>
            @endif
        @else
            <input type="text"
                   name="{{ $namePrefix }}[{{ $field['key'] }}]"
                   @if(!empty($modelPrefix)) x-model="{{ $modelPrefix }}['{{ $field['key'] }}']" @endif
                   class="form-control"
                   @if(!empty($useOld)) value="{{ old($namePrefix . '.' . $field['key'], '') }}" @endif>
        @endif
    </div>
@endforeach
