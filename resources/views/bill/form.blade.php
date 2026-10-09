<div x-data="{ billType: @js(old('bill_type', $bill->isCdr() ? 'cdr' : 'quota')) }">
    <h4>{{ __('Bill Information') }}</h4>
    <div class="form-group @error('bill_name') has-error @enderror">
        <label for="bill_name" class="col-sm-4 control-label">{{ __('Description') }}</label>
        <div class="col-sm-8">
            <input class="form-control input-sm" type="text" id="bill_name" name="bill_name" maxlength="255" required value="{{ old('bill_name', $bill->bill_name) }}">
            <span class="help-block">{{ $errors->first('bill_name') }}</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">{{ __('Billing Type') }}</label>
        <div class="col-sm-8">
            <label class="radio-inline">
                <input type="radio" name="bill_type" value="cdr" x-model="billType"> {{ __('CDR 95th') }}
            </label>
            <label class="radio-inline">
                <input type="radio" name="bill_type" value="quota" x-model="billType"> {{ __('Quota') }}
            </label>
        </div>
    </div>
    <div x-show="billType === 'cdr'">
        <div class="form-group @error('bill_cdr') has-error @enderror">
            <label class="col-sm-4 control-label" for="bill_cdr">{{ __('CDR') }}</label>
            <div class="col-sm-3">
                <input class="form-control input-sm" type="number" step="any" min="0" id="bill_cdr" name="bill_cdr" value="{{ old('bill_cdr', $form['cdr']) }}">
            </div>
            <div class="col-sm-5">
                <select name="bill_cdr_type" class="form-control input-sm" aria-label="{{ __('CDR Unit') }}">
                    @foreach(['Kbps' => __('Kilobits per second (Kbps)'), 'Mbps' => __('Megabits per second (Mbps)'), 'Gbps' => __('Gigabits per second (Gbps)')] as $unit => $label)
                        <option value="{{ $unit }}" @selected(old('bill_cdr_type', $form['cdr_type']) == $unit)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-8 col-sm-offset-4"><span class="help-block">{{ $errors->first('bill_cdr') }}</span></div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label">{{ __('95th Calculation') }}</label>
            <div class="col-sm-8">
                <label class="radio-inline">
                    <input type="radio" name="dir_95th" value="in" @checked(old('dir_95th', $bill->dir_95th) != 'agg')> {{ __('Max In/Out') }}
                </label>
                <label class="radio-inline">
                    <input type="radio" name="dir_95th" value="agg" @checked(old('dir_95th', $bill->dir_95th) == 'agg')> {{ __('Aggregate') }}
                </label>
            </div>
        </div>
    </div>
    <div x-show="billType === 'quota'">
        <div class="form-group @error('bill_quota') has-error @enderror">
            <label class="col-sm-4 control-label" for="bill_quota">{{ __('Quota') }}</label>
            <div class="col-sm-3">
                <input class="form-control input-sm" type="number" step="any" min="0" id="bill_quota" name="bill_quota" value="{{ old('bill_quota', $form['quota']) }}">
            </div>
            <div class="col-sm-5">
                <select name="bill_quota_type" class="form-control input-sm" aria-label="{{ __('Quota Unit') }}">
                    @foreach(['MB' => __('Megabytes (MB)'), 'GB' => __('Gigabytes (GB)'), 'TB' => __('Terabytes (TB)')] as $unit => $label)
                        <option value="{{ $unit }}" @selected(old('bill_quota_type', $form['quota_type']) == $unit)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-8 col-sm-offset-4"><span class="help-block">{{ $errors->first('bill_quota') }}</span></div>
        </div>
    </div>
    <div class="form-group @error('bill_day') has-error @enderror">
        <label class="col-sm-4 control-label" for="bill_day">{{ __('Billing Day') }}</label>
        <div class="col-sm-2">
            <select id="bill_day" name="bill_day" class="form-control input-sm">
                @for($day = 1; $day <= 31; $day++)
                    <option value="{{ $day }}" @selected(old('bill_day', $bill->bill_day) == $day)>{{ $day }}</option>
                @endfor
            </select>
        </div>
    </div>

    <h4>{{ __('Optional Information') }}</h4>
    <div class="form-group @error('bill_custid') has-error @enderror">
        <label class="col-sm-4 control-label" for="bill_custid">{{ __('Customer Reference') }}</label>
        <div class="col-sm-8">
            <input class="form-control input-sm" type="text" id="bill_custid" name="bill_custid" maxlength="64" value="{{ old('bill_custid', $bill->bill_custid) }}">
            <span class="help-block">{{ $errors->first('bill_custid') }}</span>
        </div>
    </div>
    <div class="form-group @error('bill_ref') has-error @enderror">
        <label class="col-sm-4 control-label" for="bill_ref">{{ __('Billing Reference') }}</label>
        <div class="col-sm-8">
            <input class="form-control input-sm" type="text" id="bill_ref" name="bill_ref" maxlength="64" value="{{ old('bill_ref', $bill->bill_ref) }}">
            <span class="help-block">{{ $errors->first('bill_ref') }}</span>
        </div>
    </div>
    <div class="form-group @error('bill_notes') has-error @enderror">
        <label class="col-sm-4 control-label" for="bill_notes">{{ __('Notes') }}</label>
        <div class="col-sm-8">
            <textarea class="form-control input-sm" id="bill_notes" name="bill_notes" maxlength="256">{{ old('bill_notes', $bill->bill_notes) }}</textarea>
            <span class="help-block">{{ $errors->first('bill_notes') }}</span>
        </div>
    </div>
</div>
