<div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl">
    @foreach($fields as $setting)
        <div class="form-group tw:mb-0"
             @if($setting['visible_if_expression']) x-show="{{ $setting['visible_if_expression'] }}" @endif>
            <label class="control-label">{{ __('poller.method_settings.' . $methodType . '.' . $setting['key']) }}</label>
            @if(($setting['field_type'] ?? 'text') === 'select')
                <select name="{{ $namePrefix }}[{{ $setting['key'] }}]"
                        @if(!empty($modelPrefix)) x-model="{{ $modelPrefix }}['{{ $setting['key'] }}']" @endif
                        class="form-control">
                    @foreach($setting['options'] ?? [] as $optVal => $optLabel)
                        <option value="{{ $optVal }}">{{ __($optLabel) }}</option>
                    @endforeach
                </select>
            @elseif(($setting['field_type'] ?? 'text') === 'number')
                <input type="number"
                       name="{{ $namePrefix }}[{{ $setting['key'] }}]"
                       @if(!empty($modelPrefix)) x-model="{{ $modelPrefix }}['{{ $setting['key'] }}']" @endif
                       class="form-control"
                       @isset($setting['min']) min="{{ $setting['min'] }}" @endisset
                       @isset($setting['max']) max="{{ $setting['max'] }}" @endisset>
            @else
                <input type="text"
                       name="{{ $namePrefix }}[{{ $setting['key'] }}]"
                       @if(!empty($modelPrefix)) x-model="{{ $modelPrefix }}['{{ $setting['key'] }}']" @endif
                       class="form-control">
            @endif
        </div>
    @endforeach
</div>
