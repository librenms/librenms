@extends('bill.layout')

@section('bill-content')
    <div class="row">
        <div class="col-lg-6 col-md-12">
            <x-panel :title="__('Bill Properties')">
                <form method="post" action="{{ route('bill.update', $bill) }}" class="form-horizontal">
                    @csrf
                    @method('PUT')
                    @include('bill.form')
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{ __('Save Properties') }}</button>
                        </div>
                    </div>
                </form>
            </x-panel>
        </div>
        <div class="col-lg-6 col-md-12">
            @include('bill.ports', ['removable' => true])

            <x-panel :title="__('Add Port')">
                <form action="{{ route('bill.port.attach', $bill) }}" method="post" class="form-horizontal">
                    @csrf
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="device">{{ __('Device') }}</label>
                        <div class="col-sm-10">
                            <select class="form-control input-sm" id="device"></select>
                        </div>
                    </div>
                    <div class="form-group @error('port_id') has-error @enderror">
                        <label class="col-sm-2 control-label" for="port_id">{{ __('Port') }}</label>
                        <div class="col-sm-10">
                            <select class="form-control input-sm" id="port_id" name="port_id" required></select>
                            <span class="help-block">{{ $errors->first('port_id') }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> {{ __('Add Port') }}</button>
                        </div>
                    </div>
                </form>
            </x-panel>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(function () {
            init_select2('#device', 'device', {}, null, @js(__('Select Device')), {width: '100%'});
            init_select2('#port_id', 'port', function (params) {
                params.device = $('#device').val();
                return params;
            }, null, @js(__('Select Port')), {width: '100%'});
            $('#device').on('change', function () {
                $('#port_id').val(null).trigger('change');
            });
        });
    </script>
@endsection
